"""CASE (MyPleasure API) tests for curated media."""
from django.utils import timezone
from django.test import TestCase
from case.models import MediaQueue, MediaStore, CustomUser
from rest_framework.test import APIClient


class CuratedMediaTestCase(TestCase):
    """Test case for MediaQueue and MediaStore."""

    def setUp(self):
        """Set up test case."""
        self.username = 'user'
        self.password = 'azertyuiop'
        self.user = CustomUser.objects.create_user(
            self.username, self.password
        )

        # Client for API calls.
        self.client = APIClient()

        # Enable usage of JWT token for authenticated tests.
        response = self.client.post('/api/v1/auth/login/', {
            'username': 'user', 'password': 'azertyuiop'
        })
        self.token = response.data['token']
        self.auth = 'Bearer {0}'.format(self.token)
        self.uri = '/api/v1/curate/fetch/{0}'.format(self.user.id)

        MediaQueue.objects.create(
            hash='h1', url='u1', requester=self.user.id,
            collection_id=self.user.collections.first().id, status='pending',
            created_at=timezone.now()
        )
        MediaQueue.objects.create(
            hash='h2', url='u2', requester=self.user.id,
            collection_id=self.user.collections.first().id, status='pending',
            created_at=timezone.now()
        )
        MediaQueue.objects.create(
            hash='h3', url='u3', requester=self.user.id,
            collection_id=self.user.collections.first().id, status='ready',
            created_at=timezone.now()
        )
        MediaQueue.objects.create(
            hash='h4', url='u4', requester=self.user.id,
            collection_id=self.user.collections.first().id, status='ready',
            created_at=timezone.now()
        )
        MediaStore.objects.create(
            hash='h3', title='t3', original_url='u3', embed_url='e3',
            poster='p3', duration='d3', naughty=False,
            created_at=timezone.now()
        )
        MediaStore.objects.create(
            hash='h4', title='t4', original_url='u4', embed_url='e4',
            poster='p4', duration='d4', naughty=False,
            created_at=timezone.now()
        )

    def test_prerequisites(self):
        """Test prerequisite for curated media tests."""
        self.assertEqual(MediaQueue.objects.all().count(), 4)
        self.assertEqual(
            MediaQueue.objects.filter(status='pending').count(), 2
        )
        self.assertEqual(MediaQueue.objects.filter(status='ready').count(), 2)
        self.assertEqual(MediaStore.objects.count(), 2)

    def test_fetch_requires_authentication(self):
        """Test GET api/v1/curate/fetch requires authentication."""
        uri = '/api/v1/curate/fetch/{0}'.format(self.user.id)
        response = self.client.get(uri)
        self.assertEqual(response.status_code, 400)

    def test_fetch_returns_ready_media(self):
        """Test GET api/v1/curate/fetch returns ready media."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.get(self.uri)
        self.client.credentials()
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['pending'], MediaStore.objects.count())

    def test_fetch_changes_jobs_statuses_in_queue(self):
        """Test GET api/v1/curate/fetch changes jobs statuses in queue."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        self.client.get(self.uri)
        self.client.credentials()
        self.assertEqual(MediaQueue.objects.get(hash='h4').status, 'done')
