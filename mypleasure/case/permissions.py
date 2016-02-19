"""CASE (MyPleasure API) permissions."""
from rest_framework import permissions


class IsCurrentUserOrReadOnly(permissions.BasePermission):
    """Object-level permission to allow only current user to edit itself."""

    def has_object_permission(self, request, view, obj):
        """
        Read permissions are allowed to any request, write is constrained.

        So we'll always allow GET, HEAD or OPTIONS requests.
        """
        if request.method in permissions.SAFE_METHODS:
            return True
        return obj == request.user
