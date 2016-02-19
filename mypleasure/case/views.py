"""CASE (MyPleasure API) views."""

from django.shortcuts import get_object_or_404

from rest_framework import status
from rest_framework.generics import (
    ListCreateAPIView, RetrieveUpdateDestroyAPIView,
)
from rest_framework.permissions import IsAuthenticated, IsAdminUser, AllowAny
from rest_framework.views import APIView
from rest_framework.viewsets import ViewSet
from rest_framework.response import Response
from rest_framework.decorators import list_route

from rest_framework_jwt.settings import api_settings

from .models import Collection, Video, CustomUser, Tag
from .permissions import IsCurrentUserOrReadOnly, IsOwnerOrReadOnly
from .serializers import BasicUserSerializer, get_user_serializer
from .serializers import CollectionSerializer, VideoSerializer
from .serializers import TagSerializer, UserRegistrationSerializer
from .filters import (
    filter_private_obj_list_by_ownership,
    filter_private_obj_detail_by_ownership
)


class UserMixin(object):
    """Mixin for User viewsets."""

    queryset = CustomUser.objects.all()
    permission_classes = (IsAuthenticated, IsCurrentUserOrReadOnly,)

    def get_serializer_class(self):
        """Different levels of serialized content based on user's status."""
        return get_user_serializer(self.request.user)


class UserList(UserMixin, ListCreateAPIView):
    """Viewset for User list."""

    permission_classes = (IsAdminUser,)


class UserDetail(UserMixin, APIView):
    """Viewset for User detail."""

    def get_serializer_class(self, pk=None):
        """Return three flavors of serializer classes based on user status."""
        user = CustomUser.objects.get(pk=pk)
        return get_user_serializer(user, pk)

    def get_object(self, pk):
        """Return CustomUser object or 404."""
        return get_object_or_404(CustomUser, pk=pk)

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


class RegistrationViewSet(ViewSet):
    """Viewset for User registration."""

    queryset = CustomUser.objects.all()

    @list_route(methods=['post'], permission_classes=[AllowAny])
    def register(self, request):
        """Validating our serializer from the UserRegistrationSerializer."""
        serializer = UserRegistrationSerializer(data=request.data)
        serializer.is_valid(raise_exception=True)

        # Everything's valid, so send it to the BasicUserSerializer
        model_serializer = BasicUserSerializer(data=serializer.data)
        model_serializer.is_valid(raise_exception=True)
        model_serializer.save()

        # Create default collection for User.
        user = CustomUser.objects.get(username=request.data['username'])
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

        return Response({'success': True, 'token': token})


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
    """Viewset for Collection detail."""

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
                    owner=CustomUser.objects.get(pk=request.user.id)
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


class TagMixin(object):
    """Mixin for Tag viewsets."""

    queryset = Tag.objects.all()
    serializer_class = TagSerializer
    permission_classes = (IsAuthenticated,)


class TagList(TagMixin, ListCreateAPIView):
    """Viewset for Tag list."""

    pass


class TagDetail(TagMixin, RetrieveUpdateDestroyAPIView):
    """Viewset for Tag detail."""

    pass


class ProfileView(APIView):
    """View for directly accessing current User's info."""

    def get(self, request, pk=None, format=None):
        """Return current user."""
        user_serializer = UserDetail(data=request.data)
        return user_serializer.get(request, pk=self.request.user.id)
