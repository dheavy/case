"""CASE (MyPleasure API) JWT helpers."""
from .serializers import UserSerializer


def jwt_response_payload_handler(token, user=None, request=None):
    """Handle response data returned after login or refresh."""
    user = UserSerializer(user).data
    user.pop('password', None)
    return {
        'token': token,
        'user': user
    }
