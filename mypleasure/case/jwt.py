"""CASE (MyPleasure API) JWT helpers."""
from .serializers import FullUserSerializer, BasicUserSerializer
from .models import CustomUser
from django.contrib.auth.models import update_last_login


def jwt_response_payload_handler(token, user=None, request=None):
    """Handle response data returned after login or refresh."""
    if user.is_staff:
        user = FullUserSerializer(user, context={'request': request}).data
    else:
        user = BasicUserSerializer(user, context={'request': request}).data

    # Update last login time for user as the JWT process does not by itself.
    update_last_login(sender=__name__, user=CustomUser.objects.get(pk=user['id']))

    return {
        'token': token,
        'user': user
    }
