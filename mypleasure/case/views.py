"""CASE (MyPleasure API) views."""

from django.shortcuts import get_object_or_404

from rest_framework import status
from rest_framework.generics import (
    ListCreateAPIView, RetrieveUpdateDestroyAPIView,
)
from rest_framework.permissions import IsAuthenticated, IsAdminUser, AllowAny
from rest_framework.exceptions import PermissionDenied
from rest_framework.views import APIView
from rest_framework.viewsets import ViewSet
from rest_framework.response import Response
from rest_framework.decorators import list_route

from rest_framework_jwt.settings import api_settings

from .models import Collection, Video, CustomUser, Tag
from .permissions import IsCurrentUserOrReadOnly
from .serializers import FullUserSerializer, BasicUserSerializer
from .serializers import CollectionSerializer, VideoSerializer
from .serializers import TagSerializer, UserRegistrationSerializer


def filter_private_obj_list_by_ownership(obj, user):
    """Filter a QuerySet based on whether user owns the object if private."""
    qsets = obj.objects.all()
    return [o for o in qsets if (not o.is_private or o.owner == user)]


def filter_private_obj_detail_by_ownership(
    queryset,
    check_object_permissions,
    request
):
    """Filter detail view object based on whether user owns it if private."""
    obj = get_object_or_404(queryset)
    check_object_permissions(request, obj)
    id = None
    try:
        id = obj.owner['id']
    except:
        id = obj.owner.id
    if obj.is_private and id != request.user.id:
        raise PermissionDenied(detail='Item is private.')
    return obj


class UserMixin(object):
    """Mixin for User viewsets."""

    queryset = CustomUser.objects.all()
    permission_classes = (IsAuthenticated, IsCurrentUserOrReadOnly,)

    def get_serializer_class(self):
        """Different levels of serialized content based on user's status."""
        if self.request.user.is_staff:
            return FullUserSerializer
        return BasicUserSerializer


class UserList(UserMixin, ListCreateAPIView):
    """Viewset for User list."""

    permission_classes = (IsAdminUser,)


class UserDetail(UserMixin, APIView):
    """Viewset for User detail."""

    def get_serializer_class(self):
        """Return two flavors of serializer classes based on user status."""
        if self.request.user.is_staff:
            return FullUserSerializer
        return BasicUserSerializer

    def get_object(self, pk):
        """Return CustomUser object or 404."""
        return get_object_or_404(CustomUser, pk=pk)

    def get(self, request, pk, format=None):
        """GET operation on user."""
        user = self.get_object(pk)
        self.check_object_permissions(request, user)
        serializer_class = self.get_serializer_class()
        serializer = serializer_class(user, context={'request': request})
        return Response(serializer.data)

    def put(self, request, pk, format=None):
        """PUT operation on user."""
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
        """DELETE operation on user."""
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

        # Return authentication token as response to log in user immediately.
        user = CustomUser.objects.get(username=request.data['username'])

        jwt_payload_handler = api_settings.JWT_PAYLOAD_HANDLER
        jwt_encode_handler = api_settings.JWT_ENCODE_HANDLER

        payload = jwt_payload_handler(user)
        token = jwt_encode_handler(payload)

        return Response({'success': True, 'token': token})


class CollectionMixin(object):
    """Mixin for Collection viewsets."""

    queryset = Collection.objects.all()
    serializer_class = CollectionSerializer
    permission_classes = (IsAuthenticated,)


class CollectionList(CollectionMixin, ListCreateAPIView):
    """Viewset for Collection list."""

    def get_queryset(self):
        """Private collections can only be seen by owner."""
        return filter_private_obj_list_by_ownership(
            Collection, self.request.user
        )


class CollectionDetail(CollectionMixin, RetrieveUpdateDestroyAPIView):
    """Viewset for Collection detail."""

    def get_queryset(self):
        """Private videos can only be seen by owner."""
        return Collection.objects.filter(pk=self.kwargs['pk'])

    def get_object(self):
        """Return data after basic checkup."""
        return filter_private_obj_detail_by_ownership(
            self.get_queryset(),
            self.check_object_permissions,
            self.request
        )


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


class ProfileView(UserDetail):
    """View for directly accessing current User's info."""

    def get(self, request, pk=None, format=None):
        """Return current user."""
        return super(ProfileView, self).get(request, self.request.user.id)
