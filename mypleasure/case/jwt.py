"""CASE (MyPleasure API) JWT helpers."""
from .serializers import UserSerializer


def jwt_response_payload_handler(token, user=None, request=None):
    """Handle response data returned after login or refresh."""
    return {
        'token': token,
        'user': UserSerializer(user).data
    }
