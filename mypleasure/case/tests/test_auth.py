"""CASE (MyPleasure API) tests for Auth."""
from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient


class AuthTestCase(TestCase):
    """Test case for auth process."""

    def setUp(self):
        """Set up test."""
        self.client = APIClient()
        self.username = 'max'
        self.password = 'azertyuiop'
        self.user = get_user_model().objects.create_user(
            self.username, self.password
        )

    def test_prerequisites(self):
        """Test prerequisite for curated media tests."""
        self.assertEqual(
            get_user_model().objects.filter(
                username=self.username
            ).count(), 1
        )

    def test_authentication_returns_token_if_successful(self):
        """Test /api/v1/auth/login returns token if successful."""
        response = self.client.post('/api/v1/auth/login/', {
            'username': self.username, 'password': self.password
        })
        self.assertContains(response, 'token', status_code=200)

    def test_refreshing_token_enables_calling_auth_methods(self):
        """
        Test /api/v1/auth/token/refresh returns new token.

        Refreshed token should enable calling an authenticated
        endpoint seamlessly.
        """
        response = self.client.post('/api/v1/auth/login/', {
            'username': self.username, 'password': self.password
        })
        self.assertContains(response, 'token', status_code=200)

        heartbeat = self.client.get('/api/v1/heartbeat/auth')
        self.assertEqual(heartbeat.status_code, 403)

        r = self.client.post('/api/v1/auth/login', {
            'username': self.username,
            'password': self.password
        })
        token = r.data['token']
        self.client.credentials(
            HTTP_AUTHORIZATION='Bearer {0}'.format(token)
        )
        heartbeat = self.client.get('/api/v1/heartbeat/auth')
        self.assertEqual(heartbeat.status_code, 200)
