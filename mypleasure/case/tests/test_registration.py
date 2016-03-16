"""CASE (MyPleasure API) tests for registration."""
from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient


class RegistrationTestCase(TestCase):
    """Test case for registration process."""

    def setUp(self):
        """Set up test."""
        self.client = APIClient()

    def test_register_fails_if_user_authenticated(self):
        """Test POST /api/v1/register/ fails if user is authenticated."""
        username = 'morgane'
        password = 'azertyuiop'
        get_user_model().objects.create_user(username, password)
        auth_response = self.client.post('/api/v1/auth/login/', {
            'username': username, 'password': password
        })
        auth_header = 'Bearer {0}'.format(auth_response.data['token'])
        self.client.credentials(HTTP_AUTHORIZATION=auth_header, format='json')

        response = self.client.post('/api/v1/register/', {
            'username': 'marion',
            'password': password,
            'confirm_password': password,
            'email': 'marion@mypleasu.re'
        })

        self.client.credentials()

        self.assertEqual(response.status_code, 400)

    def test_register_check_username_returns_if_username_exists(self):
        """
        Test GET /api/v1/register/check/username/:username.

        Should return 200 - username is already taken (found).
        """
        username = 'morgane'
        get_user_model().objects.create_user(username, 'azertyuiop')
        response_taken = self.client.get(
            '/api/v1/register/check/username/{0}'.format(username)
        )
        self.assertEqual(response_taken.status_code, 200)

    def test_register_check_username_returns_if_username_not_found(self):
        """
        Test GET /api/v1/register/check/username/.

        Should return 404 - username not taken (free to use).
        """
        response_available = self.client.get(
            '/api/v1/register/check/username/not_taken'
        )
        self.assertEqual(response_available.status_code, 404)

    def test_register_fails_if_username_missing(self):
        """Test POST /api/v1/register/ fails on missing username."""
        response = self.client.post(
            '/api/v1/register/',
            {'password': 'azertuiop', 'confirm_password': 'azertyuiop'}
        )
        self.assertEqual(response.status_code, 400)

    def test_register_fails_if_password_missing(self):
        """Test POST /api/v1/register/ fails on missing password."""
        response = self.client.post(
            '/api/v1/register/',
            {'username': 'marion', 'confirm_password': 'azertyuiop'}
        )
        self.assertEqual(response.status_code, 400)

    def test_register_fails_if_confirm_password_missing(self):
        """Test POST /api/v1/register/ fails on missing confirm_password."""
        response = self.client.post(
            '/api/v1/register/',
            {'username': 'marion', 'password': 'azertyuiop'}
        )
        self.assertEqual(response.status_code, 400)

    def test_register_fails_if_password_dont_match(self):
        """Test POST /api/v1/register/ fails on password mismatch."""
        response = self.client.post(
            '/api/v1/register/', {
                'username': 'marion',
                'password': 'tototo',
                'confirm_password': 'azertyuiop'
            }
        )
        self.assertEqual(response.status_code, 400)

    def test_register_email_not_mandatory(self):
        """Test POST /api/v1/register/ accepts blank email parameter."""
        username = 'marion'
        response = self.client.post(
            '/api/v1/register/', {
                'username': username,
                'password': 'azertyuiop',
                'confirm_password': 'azertyuiop'
            }
        )
        self.assertEqual(response.status_code, 200)
        self.assertEqual(
            get_user_model().objects.filter(username=username).count(), 1
        )

    def test_register_provides_default_email_if_successful_without_email(self):
        """
        Test POST /api/v1/register/ sets default email if none provided.

        Obviously only working on successful registration.
        """
        username = 'marion'
        response = self.client.post(
            '/api/v1/register/', {
                'username': username,
                'password': 'azertyuiop',
                'confirm_password': 'azertyuiop'
            }
        )
        u = get_user_model().objects.get(username=username)

        self.assertEqual(response.status_code, 200)
        self.assertEqual(
            u.email, u.username + '.no.email.provided@mypleasu.re'
        )

    def test_register_creates_user_not_admin_not_staff(self):
        """
        Test POST /api/v1/register/ creates a plain user.

        It's neither an admin nor a staff member.
        """
        username = 'marion'
        response = self.client.post(
            '/api/v1/register/', {
                'username': username,
                'password': 'azertyuiop',
                'confirm_password': 'azertyuiop'
            }
        )
        u = get_user_model().objects.get(username=username)

        self.assertEqual(response.status_code, 200)
        self.assertFalse(u.is_staff)
        self.assertFalse(u.is_superuser)

    def test_register_returns_token_and_user_if_successful(self):
        """
        Test POST /api/v1/register/ return payload.

        Should contain JWT token and serialized user.
        """
        username = 'marion'
        response = self.client.post(
            '/api/v1/register/', {
                'username': username,
                'password': 'azertyuiop',
                'confirm_password': 'azertyuiop'
            }
        )
        self.assertContains(response, 'token', status_code=200)
        self.assertContains(response, 'user', status_code=200)
