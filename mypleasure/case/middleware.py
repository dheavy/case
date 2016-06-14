"""CASE (MyPleasure API) middlewares."""
from django.utils.timezone import now
from .models import CustomUser


class SetLastVisitMiddleware(object):
    """Middleware for recording last access from User based on her actions."""

    def process_response(self, request, response):
        """Update last visit time after request finished processing."""
        if request.user.is_authenticated():
            CustomUser.objects.filter(pk=request.user.pk).update(
                last_access=now()
            )
        return response
