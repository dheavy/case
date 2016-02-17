"""CASE (MyPleasure API) JWT helpers."""
from .serializers import FullUserSerializer, BasicUserSerializer


def jwt_response_payload_handler(token, user=None, request=None):
    """Handle response data returned after login or refresh."""
    if user.is_staff:
        user = FullUserSerializer(user).data
    else:
        user = BasicUserSerializer(user).data
    return {
        'token': token,
        'user': user
    }
