"""CASE (MyPleasure API) views."""
import os
from django.shortcuts import get_object_or_404
from django.contrib.auth import get_user_model

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
    BasicUserSerializer, get_user_serializer, CollectionSerializer,
    VideoSerializer, TagSerializer, UserRegistrationSerializer,
    FeedVideoSerializer, PasswordResetSerializer,
    PasswordResetConfirmSerializer, CuratedMediaAcquisitionSerializer,
    CuratedMediaFetchSerializer, CheckUsernameSerializer,
    get_serialized_user_data
)
from .filters import (
    filter_private_obj_list_by_ownership,
    filter_private_obj_detail_by_ownership,
    filter_feed
)


class UserMixin(object):
    """Mixin for User viewsets."""

    queryset = get_user_model().objects.all()
    permission_classes = (IsAuthenticated, IsCurrentUserOrReadOnly,)

    def get_serializer_class(self):
        """Different levels of serialized content based on user's status."""
        return get_user_serializer(self.request.user)


class UserList(UserMixin, ListCreateAPIView):
    """Viewset for User list."""

    permission_classes = (IsAdminUser,)


class UserDetail(UserMixin, APIView):
    """View for User detail."""

    def get_serializer_class(self, pk=None):
        """Return three flavors of serializer classes based on user status."""
        user = get_user_model().objects.get(pk=pk)
        return get_user_serializer(user, pk)

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
        return Response({'detail': 'OK'}, status=status.HTTP_200_OK)

    @detail_route(
        methods=['head', 'get', 'post', 'put', 'delete', 'options']
    )
    def test_authenticated(self, request):
        """Return HTTP 200 for anyone."""
        if type(request.user) is get_user_model():
            return Response({'detail': 'OK'}, status=status.HTTP_200_OK)
        else:
            return Response(
                {'detail': 'Forbidden'}, status=status.HTTP_403_FORBIDDEN
            )


class RegistrationViewSet(ViewSet):
    """Viewset for User registration."""

    @list_route(methods=['get'], permission_classes=[AllowAny])
    def check_username(self, request, username):
        """
        Check if username is available.

        Be careful with returned status code:
        - 200 means username was found, so it's TAKEN,
        - 404 means username was not found, so it's AVAILABLE.
        """
        serializer = CheckUsernameSerializer(data={'username': username})
        serializer.is_valid(raise_exception=True)
        username = serializer.validated_data['username']

        u = get_user_model().objects.filter(username=username)
        if len(u) > 0:
            return Response(
                {'detail': 'Username taken.'}, status=status.HTTP_200_OK
            )
        else:
            return Response(
                {'detail': 'Username available.'},
                status=status.HTTP_404_NOT_FOUND
            )

    @list_route(methods=['post'], permission_classes=[AllowAny])
    def register(self, request):
        """Validating our serializer from the UserRegistrationSerializer."""
        serializer = UserRegistrationSerializer(
            data=request.data, context={'request': request}
        )
        serializer.is_valid(raise_exception=True)

        # Everything's valid, so send it to the BasicUserSerializer
        model_serializer = BasicUserSerializer(data=serializer.validated_data)
        model_serializer.is_valid(raise_exception=True)
        model_serializer.save()

        # Create default collection for User.
        user = get_user_model().objects.get(username=request.data['username'])
        collection_serializer = CollectionSerializer(
            data={'owner': user.id, 'name': 'my first collection'}
        )
        collection_serializer.is_valid(raise_exception=True)
        collection_serializer.save()

        # Return authentication token as response to log in user immediately.
        jwt_payload_handler = api_settings.JWT_PAYLOAD_HANDLER
        jwt_encode_handler = api_settings.JWT_ENCODE_HANDLER

        payload = jwt_payload_handler(user)
        token = jwt_encode_handler(payload)
        return Response({
            'user': get_serialized_user_data(user),
            'token': token
        })


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
        serializer.is_valid(raise_exception=True)
        self.perform_create(serializer)
        return Response(serializer.data, status=status.HTTP_201_CREATED)


class CollectionDetail(CollectionMixin, APIView):
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

    def put(self, request, pk, format=None):
        """PUT operation on Collection."""
        collection = self.get_object(pk)
        self.check_object_permissions(request, collection)
        serializer = self.serializer_class(
            collection, context={'request': request}
        )
        if serializer.is_valid():
            serializer.save()
            return Response(serializer.data)
        return Response(serializer.errors, status=status.HTTP_400_BAD_REQUEST)

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
                {'detail': 'Default collection cannot be deleted.'},
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
                    {'detail': 'Collection could not be deleted.'},
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


class FeedNormalList(VideoMixin, ListCreateAPIView):
    """Feed list for normal mode."""

    permission_classes = (IsAuthenticated,)
    serializer_class = FeedVideoSerializer

    def get_queryset(self):
        """Filter Feed's queryset based on naughtyness."""
        return filter_feed(obj=Video, is_naughty=False)


class FeedNaughtyList(VideoMixin, ListCreateAPIView):
    """Feed list for naughty mode."""

    permission_classes = (IsAuthenticated,)
    serializer_class = FeedVideoSerializer

    def get_queryset(self):
        """Filter Feed's queryset based on naughtyness."""
        return filter_feed(obj=Video, is_naughty=True)


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
            {'detail': 'Tag cannot be modified.'},
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
            {'detail': 'Tag cannot be deleted.'},
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
        serializer.is_valid(raise_exception=True)

        form = UserForgotPasswordForm(serializer.validated_data)
        existing_user = get_user_model().objects.filter(
            email=serializer.validated_data['email']
        )

        if not existing_user:
            return Response(
                {'detail': 'No such email in our database.'},
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
                    {'detail': 'Password reset request sent.'},
                    status=status.HTTP_200_OK
                )
            except Exception as e:
                return Response(str(e), status.HTTP_500_INTERNAL_SERVER_ERROR)
        return Response(
            {'detail': 'An error occured while resetting password.'},
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
        serializer.is_valid(raise_exception=True)
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
        serializer.is_valid(raise_exception=True)
        result = serializer.save()

        if 'code' in result and result['code'] == 'available':
            return Response({
                'detail': 'created_from_store'
            }, status=status.HTTP_200_OK)
        if 'code' in result and result['code'] == 'added':
            return Response({
                'detail': 'added_to_queue'
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
        serializer.is_valid(raise_exception=True)

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
