"""CASE (MyPleasure API) tests for curated media."""
from django.utils import timezone
from django.test import TestCase
from case.models import (
    MediaQueue, MediaStore, CustomUser, Collection, Video
)
from rest_framework.test import APIClient


class CuratedMediaTestCase(TestCase):
    """Test case for MediaQueue and MediaStore."""

    def setUp(self):
        """Set up test case."""
        username = 'morgane'
        password = 'azertyuiop'
        self.user = CustomUser.objects.create_user(username, password)

        self.user2 = CustomUser.objects.create_user('marion', 'azertyiop')

        # Client for API calls.
        self.client = APIClient()

        # Enable usage of JWT token for authenticated tests.
        response = self.client.post('/api/v1/auth/login/', {
            'username': username, 'password': password
        })
        self.token = response.data['token']
        self.auth = 'Bearer {0}'.format(self.token)

        self.fetch_uri = '/api/v1/curate/fetch/{0}'.format(self.user.id)
        self.acquire_uri = '/api/v1/curate/acquire'
        self.dummy_uri = 'http://example.com'

        MediaQueue.objects.create(
            hash='h1', url=self.dummy_uri, requester=self.user.id,
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
        """GET api/v1/curate/fetch requires authentication."""
        response = self.client.get(self.fetch_uri)
        self.assertEqual(response.status_code, 400)

    def test_fetch_needs_user_id(self):
        """GET api/v1/curate/fetch returns error if no ID param."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.get('/api/v1/curate/fetch/')
        self.client.credentials()
        self.assertEqual(response.status_code, 404)

    def test_fetch_needs_current_user_id(self):
        """GET api/v1/curate/fetch returns error if ID isn't current user's."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.get(
            '/api/v1/curate/fetch/{0}'.format(self.user2.id)
        )
        self.client.credentials()
        self.assertEqual(response.status_code, 400)

    def test_fetch_returns_ready_media(self):
        """GET api/v1/curate/fetch returns ready media."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.get(self.fetch_uri)
        self.client.credentials()
        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.data['code'], 'fetched')
        self.assertEqual(response.data['pending'], MediaStore.objects.count())

    def test_fetch_changes_jobs_statuses_in_queue(self):
        """GET api/v1/curate/fetch changes jobs statuses in queue."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        self.client.get(self.fetch_uri)
        self.client.credentials()
        self.assertEqual(MediaQueue.objects.get(hash='h4').status, 'done')

    def test_fetch_returns_code_empty_when_no_new_videos(self):
        """GET api/v1/curate/fetch in payload returns 'empty' if no videos."""
        mediae = [m for m in MediaQueue.objects.all()]
        for m in mediae:
            m.status = 'pending'
            m.save()

        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.get(self.fetch_uri)
        self.client.credentials()

        self.assertEqual(MediaQueue.objects.filter(status='done').count(), 0)
        self.assertEqual(response.data['code'], 'empty')

    def test_acquire_requires_authentication(self):
        """GET api/v1/curate/acquire requires authentication."""
        response = self.client.post(self.acquire_uri, {})
        self.assertEqual(response.status_code, 400)

    def test_acquire_requires_collection_id_or_new_collection_name_param(self):
        """
        POST api/v1/curate/acquire fails without required parameters.

        Parameters should either be a collection ID or a name (string).
        """
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.post(self.acquire_uri, {
            'url': 'http://youtube.com'
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'collection_id_or_name_missing'
        )

    def test_acquire_new_collection_name_fails_if_name_is_empty_or_none(self):
        """
        POST api/v1/curate/acquire fails if name is empty or none.

        Triggered only if collection_id not passed.
        """
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.post(self.acquire_uri, {
            'new_collection_name': '',
            'url': 'http://youtube.com'
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'collection_id_or_name_missing'
        )

        response = self.client.post(self.acquire_uri, {
            'new_collection_name': '',
            'url': None
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'collection_id_or_name_missing'
        )

    def test_acquire_returns_validation_error_if_collection_not_owned(self):
        """
        POST api/v1/curate/acquire fails without required parameters.

        If parameter given is collection ID, the collection should exist and
        belong to the current user.
        """
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.post(self.acquire_uri, {
            'collection_id': 99,
            'url': 'http://youtube.com'
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'collection_id_invalid'
        )

        c = Collection.objects.get(owner=self.user2)

        response = self.client.post(self.acquire_uri, {
            'collection_id': c.id,
            'url': 'http://youtube.com'
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'collection_id_invalid'
        )

    def test_acquire_requires_url_param(self):
        """POST api/v1/curate/acquire requires 'url' parameter."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.post(self.acquire_uri, {
            'collection_id': self.user.collections.first().id,
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'url_missing'
        )

    def test_acquire_returns_validation_error_if_url_invalid(self):
        """POST api/v1/curate/acquire returns validation error on bad URL."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.post(self.acquire_uri, {
            'collection_id': self.user.collections.first().id,
            'url': None
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'url_invalid'
        )

        response = self.client.post(self.acquire_uri, {
            'collection_id': self.user.collections.first().id,
            'url': 'not a url'
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'url_invalid'
        )

    def test_acquire_returns_validation_error_if_duplicate(self):
        """POST api/v1/curate/acquire returns validation on duplicates."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.post(self.acquire_uri, {
            'collection_id': self.user.collections.first().id,
            'url': self.dummy_uri
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'duplicate'
        )

        Video.objects.create(
            hash='somehash', title='my title',
            original_url=self.dummy_uri,
            collection_id=self.user.collections.first().id
        )
        response = self.client.post(self.acquire_uri, {
            'collection_id': self.user.collections.first().id,
            'url': self.dummy_uri
        })
        self.assertEqual(response.status_code, 400)
        self.assertEqual(
            response.data[0]['detail'], 'duplicate'
        )

    def test_acquire_adds_to_queue(self):
        """Successful POST api/v1/curate/acquire adds job to queue."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth, format='json')
        response = self.client.post(self.acquire_uri, {
            'collection_id': self.user.collections.first().id,
            'url': 'http://example.com/successful'
        })
        self.assertEqual(response.status_code, 200)
        self.assertEqual(
            response.data['detail'], 'added_to_queue'
        )

    def test_acquire_returns_copy_from_store_if_exists(self):
        """
        POST api/v1/curate/acquire uses media store.

        On a successful request, check if video already exists cached in store,
        and return it if it does.
        """
        pass
