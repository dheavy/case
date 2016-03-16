"""CASE (MyPleasure API) tests for heartbeats."""
from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient


class HeartbeatTestCase(TestCase):
    """
    Test case for heartbeats.

    Heartbeats are dummy API endpoints for testing reactiveness/life.
    """

    def setUp(self):
        """Set up test."""
        self.client = APIClient()

    def test_unauthenticated_heartbeat_returns_200(self):
        """Test GET|POST|PUT|DELETE /api/v1/heartbeat returns 200."""
        url = '/api/v1/heartbeat/'

        get = self.client.get(url)
        post = self.client.post(url)
        put = self.client.put(url)
        delete = self.client.delete(url)

        self.assertEqual(get.status_code, 200)
        self.assertEqual(post.status_code, 200)
        self.assertEqual(put.status_code, 200)
        self.assertEqual(delete.status_code, 200)

    def test_authenticated_heartbeat_returns_ok(self):
        """
        Test GET|POST|PUT|DELETE /api/v1/heartbeat/auth returns 200.

        Works only for authenticated user.
        """
        url = '/api/v1/heartbeat/auth'

        get = self.client.get(url)
        post = self.client.post(url)
        put = self.client.put(url)
        delete = self.client.delete(url)

        self.assertEqual(get.status_code, 403)
        self.assertEqual(post.status_code, 403)
        self.assertEqual(put.status_code, 403)
        self.assertEqual(delete.status_code, 403)

        get_user_model().objects.create_user('max', 'azertyuiop')
        r = self.client.post('/api/v1/auth/login', {
            'username': 'max',
            'password': 'azertyuiop'
        })
        token = r.data['token']
        self.client.credentials(
            HTTP_AUTHORIZATION='Bearer {0}'.format(token)
        )
        get = self.client.get(url)
        post = self.client.post(url)
        put = self.client.put(url)
        delete = self.client.delete(url)

        self.assertEqual(get.status_code, 200)
        self.assertEqual(post.status_code, 200)
        self.assertEqual(put.status_code, 200)
        self.assertEqual(delete.status_code, 200)
