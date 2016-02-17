"""MyPleasure API Views."""

from django.contrib.auth.models import User
from django.shortcuts import get_object_or_404

from rest_framework.generics import (
    ListCreateAPIView, RetrieveUpdateDestroyAPIView
)
from rest_framework.permissions import IsAuthenticated, IsAdminUser

from .models import Collection, Video
from .serializers import FullUserSerializer, BasicUserSerializer
from .serializers import CollectionSerializer
from .serializers import VideoSerializer


def filter_private_obj_by_ownership(obj, user):
    """Filter a QuerySet based on whether user is admin or owns the object."""
    qsets = obj.objects.all()
    return [o for o in qsets if (not o.is_private or o.owner == user)]


class UserMixin(object):
    """Mixin for User viewsets."""

    queryset = User.objects.all()
    permission_classes = (IsAuthenticated,)

    def get_serializer_class(self):
        """Different levels of serialized content based on user's status."""
        if self.request.user.is_staff:
            return FullUserSerializer
        return BasicUserSerializer


class UserList(UserMixin, ListCreateAPIView):
    """Viewset for User list."""

    permission_classes = (IsAdminUser,)


class UserDetail(UserMixin, RetrieveUpdateDestroyAPIView):
    """Viewset for User detail."""

    pass


class ProfileView(UserDetail):
    """View for directly accessing current User's info."""

    def get_queryset(self):
        """Filter queryset to return current user's data."""
        return User.objects.filter(pk=self.request.user.id)

    def get_object(self):
        """Return data after basic checkup."""
        queryset = self.get_queryset()
        obj = get_object_or_404(queryset)
        self.check_object_permissions(self.request, obj)
        return obj


class CollectionMixin(object):
    """Mixin for Collection viewsets."""

    queryset = Collection.objects.all()
    serializer_class = CollectionSerializer
    permission_classes = (IsAuthenticated,)


class CollectionList(CollectionMixin, ListCreateAPIView):
    """Viewset for Collection list."""

    def get_queryset(self):
        """Private collections can only be seen by owner."""
        return filter_private_obj_by_ownership(Collection, self.request.user)


class CollectionDetail(CollectionMixin, RetrieveUpdateDestroyAPIView):
    """Viewset for Collection detail."""

    pass


class VideoMixin(object):
    """Mixin for Video viewsets."""

    queryset = Video.objects.all()
    serializer_class = VideoSerializer
    permission_classes = (IsAuthenticated,)


class VideoList(VideoMixin, ListCreateAPIView):
    """Viewset for Video list."""

    def get_queryset(self):
        """Private videos can only be seen by owner."""
        return filter_private_obj_by_ownership(Video, self.request.user)


class VideoDetail(VideoMixin, RetrieveUpdateDestroyAPIView):
    """Viewset for Video detail."""

    pass
