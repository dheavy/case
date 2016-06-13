"""CASE (MyPleasure API) views."""
import os
import crypt
from django.shortcuts import get_object_or_404
from django.contrib.auth import get_user_model
from django.http.request import QueryDict
from rest_framework import status
from rest_framework.generics import (
    ListCreateAPIView, RetrieveUpdateDestroyAPIView, GenericAPIView
)
from rest_framework.permissions import IsAdminUser, AllowAny, IsAuthenticated
from rest_framework.views import APIView
from rest_framework.viewsets import ViewSet
from rest_framework.response import Response
from rest_framework.decorators import list_route, detail_route
from rest_framework_jwt.settings import api_settings

from case.models import (
    Collection, Video, Tag, MediaStore, MediaQueue
)
from case.forms import UserForgotPasswordForm
from .permissions import (
    IsCurrentUserOrReadOnly, IsOwnerOrReadOnly
)
from .serializers import (
    BasicUserSerializer, user_serializer, CollectionSerializer,
    VideoSerializer, TagSerializer, UserRegistrationSerializer,
    FeedNormalSerializer, FeedNaughtySerializer, PasswordResetSerializer,
    PasswordResetConfirmSerializer, CuratedMediaAcquisitionSerializer,
    CuratedMediaFetchSerializer, CheckUsernameSerializer, FollowUserSerializer,
    BlockUserSerializer, FollowCollectionSerializer, BlockCollectionSerializer,
    FacebookUserSerializer, EditPasswordSerializer, serialized_user_data
)
from .filters import (
    filter_private_obj_list_by_ownership,
    filter_private_obj_detail_by_ownership
)


def create_auth_token_payload(user):
    """Return authentication token as response to log in user process."""
    jwt_payload_handler = api_settings.JWT_PAYLOAD_HANDLER
    jwt_encode_handler = api_settings.JWT_ENCODE_HANDLER

    payload = jwt_payload_handler(user)
    token = jwt_encode_handler(payload)

    return {
        'user': serialized_user_data(user),
        'token': token
    }


class UserMixin(object):
    """Mixin for User viewsets."""

    queryset = get_user_model().objects.all()
    permission_classes = (IsAuthenticated, IsCurrentUserOrReadOnly,)

    def get_serializer_class(self):
        """Different levels of serialized content based on user's status."""
        return user_serializer(self.request.user)


class UserList(UserMixin, ListCreateAPIView):
    """Viewset for User list."""

    permission_classes = (IsAdminUser,)


class UserDetail(UserMixin, APIView):
    """View for User detail."""

    def get_serializer_class(self, pk=None):
        """Return three flavors of serializer classes based on user status."""
        user = get_user_model().objects.get(pk=pk)
        return user_serializer(user, pk)

    def get_object(self, pk):
        """Return CustomUser object or 404."""
        return get_object_or_404(get_user_model(), pk=pk)

    def get(self, request, pk, format=None):
        """GET operation on User."""
        user = self.get_object(pk)
        self.check_object_permissions(request, user)
        serializer_class = self.get_serializer_class(pk=user.id)
        serializer = serializer_class(user, context={'request': request})
        return Response(serializer.data)

    def put(self, request, pk, format=None):
        """PUT operation on User."""
        user = self.get_object(pk)
        self.check_object_permissions(request, user)
        serializer_class = self.get_serializer_class()
        serializer = serializer_class(
            user, context={'request': request}, data=request.data, partial=True
        )
        if serializer.is_valid():
            serializer.save()
            return Response(serializer.data)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

    def delete(self, request, pk, format=None):
        """DELETE operation on User."""
        user = self.get_object(pk=pk)
        self.check_object_permissions(request, user)
        user.disable_account()
        return Response(status=status.HTTP_204_NO_CONTENT)


class EditAccountViewSet(ViewSet):
    """ViewSet for direct account edits (password, email)."""

    @detail_route(methods=['post'], permission_classes=[IsOwnerOrReadOnly])
    def edit_password(self, request):
        """Edit user's password."""
        serializer = EditPasswordSerializer(data=request.data)
        if serializer.is_valid():
            serializer.save()
            return Response(
                {'message': 'Password changed successfully'},
                status=status.HTTP_200_OK
            )
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

    @detail_route(methods=['post'], permission_classes=[IsOwnerOrReadOnly])
    def edit_email(self, request):
        """Edit user's email."""
        pass


class HeartbeatViewSet(ViewSet):
    """
    Simple Viewset for testing API responsiveness.

    TODO: -> https://github.com/tomchristie/django-rest-framework/issues/3997
    This issue seems to be what I'm going through here, with permissions
    in decorators not working.
    At some point, this needs a fix.
    """

    @detail_route(
        methods=['head', 'get', 'post', 'put', 'delete', 'options']
    )
    def test(self, request):
        """Return HTTP 200 for anyone."""
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)

    @detail_route(
        methods=['head', 'get', 'post', 'put', 'delete', 'options']
    )
    def test_authenticated(self, request):
        """Return HTTP 200 for anyone."""
        if type(request.user) is get_user_model():
            return Response({'message': 'OK'}, status=status.HTTP_200_OK)
        else:
            return Response(
                {'message': 'Forbidden'}, status=status.HTTP_403_FORBIDDEN
            )


class RegistrationViewSet(ViewSet):
    """Viewset for User registration."""

    @list_route(methods=['get'], permission_classes=[AllowAny])
    def check_username(self, request, username):
        """
        Check if username is available.

        Be careful with returned status code:
        - 206 (Partial Content) if username is taken,
        - 200 (OK) if username is available.
        """
        serializer = CheckUsernameSerializer(data={'username': username})
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        username = serializer.validated_data['username']

        u = get_user_model().objects.filter(username=username)
        if len(u) > 0:
            return Response(
                {'message': 'Username taken.'},
                status=status.HTTP_206_PARTIAL_CONTENT
            )
        else:
            return Response(
                {'message': 'Username available.'},
                status=status.HTTP_200_OK
            )

    @list_route(methods=['post'], permission_classes=[AllowAny])
    def register(self, request):
        """Register user with username/password."""
        user = self.registration_process(request)

        # Return authentication token as response to log in user immediately.
        return Response(
            create_auth_token_payload(user), status=status.HTTP_201_CREATED
        )

    @list_route(methods=['post'], permission_classes=[AllowAny])
    def facebook_register(self, request):
        """Finish registration using Facebook account."""
        try:
            # Attach fake password and confirmation to bypass password
            # validation, as for this case we will never need the password
            # to log in the user.
            data = dict(request.data)
            pwd = crypt.crypt(
                data.get('fb_id')[0],
                crypt.METHOD_MD5
            )
            data['password'] = pwd
            data['confirm_password'] = pwd
            qdict = QueryDict('', mutable=True)
            qdict.update(data)

            serializer = FacebookUserSerializer(data=request.data)
            try:
                serializer.is_valid(raise_exception=True)
            except:
                return Response(
                    serializer.errors, status.HTTP_400_BAD_REQUEST
                )
            user = serializer.finish_create()
            return Response(
                create_auth_token_payload(user), status=status.HTTP_201_CREATED
            )
        except Exception as e:
            return Response(e, status=status.HTTP_400_BAD_REQUEST)

    def registration_process(self, request):
        """Register a new user."""
        serializer = UserRegistrationSerializer(
            data=request.data, context={'request': request}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )

        # Everything's valid, so send it to the BasicUserSerializer
        model_serializer = BasicUserSerializer(data=serializer.validated_data)
        try:
            model_serializer.is_valid(raise_exception=True)
        except:
            return Response(
                model_serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        model_serializer.save()
        user = get_user_model().objects.get(username=request.data['username'])

        return user


class FollowUserViewSet(ViewSet):
    """ViewSet for user <-> user follow relationship."""

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def follow(self, request, pk):
        """Follow a user."""
        serializer = FollowUserSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'follow'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def unfollow(self, request, pk):
        """Unfollow a user."""
        serializer = FollowUserSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'unfollow'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)


class BlockUserViewSet(ViewSet):
    """ViewSet for user <-> user block relationship."""

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def block(self, request, pk):
        """Block a user."""
        serializer = BlockUserSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'block'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def unblock(self, request, pk):
        """Unblock a user."""
        serializer = BlockUserSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'unblock'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)


class FollowCollectionViewSet(ViewSet):
    """ViewSet for user <-> collection follow relationship."""

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def follow(self, request, pk):
        """Follow a collection."""
        serializer = FollowCollectionSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'follow'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def unfollow(self, request, pk):
        """Unfollow a collection."""
        serializer = FollowCollectionSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'unfollow'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)


class BlockCollectionViewSet(ViewSet):
    """ViewSet for user <-> collection block relationship."""

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def block(self, request, pk):
        """Block a collection."""
        serializer = BlockCollectionSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'block'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)

    @list_route(methods=['post'], permission_classes=[IsAuthenticated])
    def unblock(self, request, pk):
        """Unblock a collection."""
        serializer = BlockCollectionSerializer(
            data={'pk': pk, 'current_user': request.user, 'intent': 'unblock'}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        return Response({'message': 'OK'}, status=status.HTTP_200_OK)


class CollectionMixin(object):
    """Mixin for Collection viewsets."""

    queryset = Collection.objects.all()
    serializer_class = CollectionSerializer
    permission_classes = (IsAuthenticated, IsOwnerOrReadOnly,)


class CollectionList(CollectionMixin, ListCreateAPIView):
    """Viewset for Collection list."""

    def get_queryset(self):
        """Private collections can only be seen by owner."""
        return filter_private_obj_list_by_ownership(
            Collection, self.request.user
        )

    def perform_create(self, serializer):
        """Effectively save instance."""
        serializer.save()

    def create(self, request, *args, **kwargs):
        """GET operation on Collection."""
        data = request.data.copy()
        data.update({'owner': self.request.user.id})
        serializer = self.get_serializer(data=data)
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        self.perform_create(serializer)
        return Response(serializer.data, status=status.HTTP_201_CREATED)


class CollectionDetail(CollectionMixin, RetrieveUpdateDestroyAPIView):
    """View for Collection detail."""

    def get_queryset(self):
        """
        Collection queryset.

        Private videos attached to this collection can only be seen by owner.
        """
        return Collection.objects.filter(pk=self.kwargs['pk'])

    def get_object(self):
        """Return data after basic checkup."""
        return filter_private_obj_detail_by_ownership(
            self.get_queryset(),
            self.check_object_permissions,
            self.request
        )

    def get(self, request, pk, format=None):
        """GET operation on Collection."""
        collection = self.get_object()
        self.check_object_permissions(request, collection)
        serializer = self.serializer_class(
            collection, context={'request': request}
        )
        return Response(serializer.data, status=status.HTTP_200_OK)

    def delete(self, request, pk, format=None):
        """
        DELETE operation on Collection.

        User can NOT delete her default collection.
        """
        collection = self.get_object()
        self.check_object_permissions(request, collection)

        # Ensure default collection cannot be deleted without special consent.
        if collection.is_default and 'force_deletion' not in request.data:
            return Response(
                {'message': 'Default collection cannot be deleted.'},
                status=status.HTTP_403_FORBIDDEN
            )

        # If a replacement collection ID was passed, move the videos there.
        if 'replacement' in request.data:
            try:
                replacement_collection = Collection.objects.get(
                    pk=request.data['replacement'],
                    owner=get_user_model().objects.get(pk=request.user.id)
                )
                for v in collection.videos.all():
                    v.collection = replacement_collection
                    v.save()
            except Exception:
                return Response(
                    {'message': 'Collection could not be deleted.'},
                    status=status.HTTP_400_BAD_REQUEST
                )

        collection.delete()
        return Response(status=status.HTTP_204_NO_CONTENT)


class VideoMixin(object):
    """Mixin for Video viewsets."""

    queryset = Video.objects.all()
    serializer_class = VideoSerializer
    permission_classes = (IsAuthenticated,)


class VideoList(VideoMixin, ListCreateAPIView):
    """Viewset for Video list."""

    def get_queryset(self):
        """Private videos can only be seen by owner."""
        return filter_private_obj_list_by_ownership(Video, self.request.user)

    def post(self, request, *args, **kwargs):
        """Attempt creating a new video."""
        collection = None
        try:
            collection = Collection.objects.get(
                pk=int(request.data.get('collection'))
            )
        except:
            return Response(
                'Collection does not exist', status=status.HTTP_404_NOT_FOUND
            )

        if collection in request.user.collections.all():
            serializer = self.get_serializer_class()(data=request.data)
            if serializer.is_valid():
                serializer.save()
                return Response(
                    serializer.data, status=status.HTTP_201_CREATED
                )
            return Response(
                serializer.errors, status=status.HTTP_400_BAD_REQUEST
            )
        else:
            return Response(
                'Collection not owned', status=status.HTTP_403_FORBIDDEN
            )


class VideoDetail(VideoMixin, RetrieveUpdateDestroyAPIView):
    """Viewset for Video detail."""

    def get_queryset(self):
        """Private videos can only be seen by owner."""
        return Video.objects.filter(pk=self.kwargs['pk'])

    def get_object(self):
        """Return data after basic checkup."""
        return filter_private_obj_detail_by_ownership(
            self.get_queryset(),
            self.check_object_permissions,
            self.request
        )

    def delete(self, request, *args, **kwargs):
        """Attempt at deleting video."""
        video = None
        try:
            video = Video.objects.get(pk=int(kwargs.get('pk')))
        except:
            return Response(
                'Video does not exist', status=status.HTTP_404_NOT_FOUND
            )

        if video in request.user.videos:
            video.delete()
            return Response(
                {'message': 'OK'}, status=status.HTTP_204_NO_CONTENT
            )
        else:
            return Response(
                {'message': 'Video not owned'}, status=status.HTTP_403_FORBIDDEN
            )


class FeedNormalList(VideoMixin, ListCreateAPIView):
    """Feed list for normal mode."""

    permission_classes = (IsAuthenticated,)
    queryset = Video.objects.all()

    def list(self, request):
        """Return payload."""
        serializer = FeedNormalSerializer(
            self.get_queryset(),
            many=True, context={'request': request}
        )
        return Response(serializer.data[0])


class FeedNaughtyList(VideoMixin, ListCreateAPIView):
    """Feed list for naughty mode."""

    permission_classes = (IsAuthenticated,)
    queryset = Video.objects.all()

    def list(self, request):
        """Return payload."""
        serializer = FeedNaughtySerializer(
            self.get_queryset(),
            many=True, context={'request': request}
        )
        return Response(serializer.data[0])


class TagMixin(object):
    """Mixin for Tag viewsets."""

    queryset = Tag.objects.all()
    serializer_class = TagSerializer
    permission_classes = (IsAuthenticated,)


class TagList(TagMixin, ListCreateAPIView):
    """Viewset for Tag list."""

    pass


class TagDetail(TagMixin, APIView):
    """View for Tag detail."""

    def get_object(self, pk):
        """Return Tag object or 404."""
        return get_object_or_404(Tag, pk=pk)

    def get(self, request, pk=None, format=None):
        """GET operation on Collection."""
        tag = self.get_object(pk)
        self.check_object_permissions(request, tag)
        serializer = self.serializer_class(
            tag, context={'request': request}
        )
        return Response(serializer.data, status=status.HTTP_200_OK)

    def put(self, request, pk=None, format=None):
        """PUT operation on Collection."""
        if self.request.user.is_staff:
            tag = self.get_object(pk)
            self.check_object_permissions(request, tag)
            serializer = self.serializer_class(
                tag, context={'request': request}
            )
            if serializer.is_valid():
                serializer.save()
                return Response(serializer.data)
            return Response(
                serializer.errors, status=status.HTTP_400_BAD_REQUEST
            )
        return Response(
            {'message': 'Tag cannot be modified.'},
            status=status.HTTP_403_FORBIDDEN
        )

    def delete(self, request, pk=None, format=None):
        """DELETE operation on User."""
        if self.request.user.is_staff:
            tag = self.get_object(pk=pk)
            self.check_object_permissions(request, tag)
            tag.delete()
            return Response(status=status.HTTP_204_NO_CONTENT)
        return Response(
            {'message': 'Tag cannot be deleted.'},
            status=status.HTTP_403_FORBIDDEN
        )


class ProfileView(APIView):
    """View for directly accessing current User's info."""

    def get(self, request, pk=None, format=None):
        """Return current user."""
        user_serializer = UserDetail(data=request.data)
        return user_serializer.get(request, pk=self.request.user.id)


class PasswordResetView(GenericAPIView):
    """
    Calls Django Auth PasswordResetForm save method.

    Accepts the following POST parameters: email
    Returns the success/fail message.
    """

    serializer_class = PasswordResetSerializer
    permission_classes = (AllowAny,)

    def post(self, request, *args, **kwargs):
        """Create a serializer with request.data."""
        serializer = self.get_serializer(data=request.data)
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )

        form = UserForgotPasswordForm(serializer.validated_data)
        existing_user = get_user_model().objects.filter(
            email=serializer.validated_data['email']
        )

        if not existing_user:
            return Response(
                {'message': 'No such email in our database.'},
                status.HTTP_500_INTERNAL_SERVER_ERROR
            )

        if form.is_valid():
            try:
                path = os.path.join(
                    os.path.dirname(
                        os.path.abspath(__file__ + '../../')
                    ), 'templates/registration/password_reset_email.html'
                )
                form.save(
                    from_email='no-reply@mypleasu.re',
                    email_template_name=path,
                    request=request
                )
                return Response(
                    {'message': 'Password reset request sent.'},
                    status=status.HTTP_200_OK
                )
            except Exception as e:
                return Response(str(e), status.HTTP_500_INTERNAL_SERVER_ERROR)
        return Response(
            {'message': 'An error occured while resetting password.'},
            status.HTTP_500_INTERNAL_SERVER_ERROR
        )


class PasswordResetConfirmView(GenericAPIView):
    """
    Password reset e-mail link is confirmed: resets the user's password.

    Accepts the following POST parameters: new_password1, new_password2
    Accepts the following Django URL arguments: token, uid
    Returns the success/fail message.
    """

    serializer_class = PasswordResetConfirmSerializer
    permission_classes = (AllowAny,)

    def post(self, request, uidb64=None, token=None):
        """Post confirmation."""
        data = {
            'new_password1': request.data.get('password', None),
            'new_password2': request.data.get('confirm_password', None),
            'token': token,
            'uid': uidb64
        }
        serializer = self.get_serializer(data=data)
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        serializer.save()
        return Response({
            'success': 'Password has been reset with the new password.'
        }, status=status.HTTP_200_OK)


class CuratedMediaViewSet(ViewSet):
    """ViewSet for curated media management."""

    permission_classes = (IsAuthenticated,)

    @list_route(methods=['post'])
    def acquire(self, request):
        """
        Media acquisition.

        Queues a media for processing.
        If collection ID is provided and belongs to user,
        make the media belong to it. Otherwise, use new_collection_name
        to create a new collection to add the media request to.
        """
        serializer = CuratedMediaAcquisitionSerializer(
            data=request.data,
            context={'request': request}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )
        result = serializer.save()

        if 'code' in result and result['code'] == 'available':
            return Response({
                'message': 'created_from_store'
            }, status=status.HTTP_200_OK)
        if 'code' in result and result['code'] == 'added':
            return Response({
                'message': 'added_to_queue'
            }, status=status.HTTP_200_OK)

    @list_route(methods=['get'], permission_classes=[IsAuthenticated])
    def fetch(self, request, userid=None):
        """
        Media fetching.

        Fetch videos processed for user and ready to be added
        to her collections. For convenience, return also the number
        of videos currently pending process.
        """
        serializer = CuratedMediaFetchSerializer(
            data={'userid': userid},
            context={'request': request}
        )
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )

        new_and_ready = self.fetch_new_and_ready(
            serializer.validated_data['userid']
        )
        pending = self.get_pending_number(serializer.validated_data['userid'])
        status_code = status.HTTP_200_OK

        if new_and_ready is True:
            code = 'fetched'
        elif new_and_ready is False:
            status_code = status.HTTP_500_SERVER_ERROR
            code = 'error'
        elif new_and_ready is None:
            code = 'empty'

        return Response({
            'pending': pending,
            'code': code
        }, status=status_code)

    def fetch_new_and_ready(self, id):
        """Fetch new videos recently acquired."""
        ready_mediae = MediaQueue.objects.filter(
            status='ready', requester=id
        )

        # 'None' returned if no videos are found.
        if len(ready_mediae) == 0:
            return None

        # Create and assign new Video instance
        # from MediaStore instance if something was found.
        # Update job status in queue.

        for media in ready_mediae:
            try:
                stored_media = MediaStore.objects.get(hash=media.hash)
                video = Video(**stored_media.as_video_params())
                video.collection = Collection.objects.get(
                    pk=media.collection_id
                )
                video.save()

                media.status = 'done'
                media.save()
            except:
                pass

        return True

    def get_pending_number(self, id):
        """Return number of videos currently pending process for user."""
        return MediaQueue.objects.filter(
            status='pending',
            requester=id
        ).count()


class FacebookAuthViewSet(ViewSet):
    """
    FacebookAuthViewSet.

    Controls the Facebook-based authentication and registration processes.
    """

    @list_route(methods=['post'], permission_class=[AllowAny])
    def auth(self, request, *args, **kwargs):
        """
        Authenticate, or create then authenticate, a Facebook user.

        Get available user infos from FB API and check if they match
        with a FacebookUser attached to a CustomUser model.
        If they do: we have a user using her FB account to log in - proceed.
        If they don't: create a new user from a FB account then log in.
        """
        serializer = FacebookUserSerializer(data=request.data)
        try:
            serializer.is_valid(raise_exception=True)
        except:
            return Response(
                serializer.errors, status.HTTP_400_BAD_REQUEST
            )

        # Returns either a payload to finish registration,
        # or a user if we can proceed with login.
        p = serializer.create_or_authenticate()

        # If it's a first time registration, create a CustomUser then
        # return the necessary payload to complete it via frontend
        # (see 'facebook-register' route). The status code the frontend looks
        # for is 206 ("Partial Content").
        if 'intent' in p and p.get('intent') == 'facebook_register':
            return Response(p, status=status.HTTP_206_PARTIAL_CONTENT)

        # ...or an authentication token as response to log in user immediately.
        return Response(
            create_auth_token_payload(p.get('user')), status=status.HTTP_200_OK
        )
