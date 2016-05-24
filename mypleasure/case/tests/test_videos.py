"""CASE (MyPleasure API) tests for Video."""
from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient
from case.models import Video


class VideoTestCase(TestCase):
    """Test case for Video model."""

    def setUp(self):
        """Set up tests."""
        self.client = APIClient()
        self.u1 = get_user_model().objects.create_user(
            'morgane', 'azertyuiop'
        )
        self.u2 = get_user_model().objects.create_user(
            'marion', 'azertyuiop'
        )

        self.u2.collections.create(name='private collection', is_private=True)

        Video.objects.create(
            hash='m1', title='marion\'s video 1',
            original_url='url',
            collection_id=self.u2.collections.first().id
        )

        Video.objects.create(
            hash='m2', title='marion\'s video 1',
            original_url='url',
            collection_id=self.u2.collections.first().id
        )

        Video.objects.create(
            hash='m3', title='marion\'s video 1',
            original_url='url',
            collection_id=self.u2.collections.first().id
        )

        Video.objects.create(
            hash='m4', title='marion\'s video 1',
            original_url='url',
            collection_id=self.u2.collections.last().id
        )

        self.uri = '/api/v1/videos/'
        response1 = self.client.post('/api/v1/auth/login/', {
            'username': 'morgane', 'password': 'azertyuiop'
        })
        self.token1 = response1.data['token']
        self.auth1 = 'Bearer {0}'.format(self.token1)

        response2 = self.client.post('/api/v1/auth/login/', {
            'username': 'marion', 'password': 'azertyuiop'
        })
        self.token2 = response2.data['token']
        self.auth2 = 'Bearer {0}'.format(self.token2)

    # def test_video_list_requires_authentication(self):
    #     """Test /api/v1/videos/ requires authentication."""
    #     pass

    def test_video_detail_requires_authentication(self):
        """Test GET/api/v1/videos/:id requires authentication."""
        r = self.client.get(self.uri + str(self.u2.videos[0].id))
        self.assertEqual(r.status_code, 403)

        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.get(self.uri + str(self.u2.videos[0].id))
        self.assertEqual(r.status_code, 200)

    def test_video_detail_hides_private_videos_if_not_owner(self):
        """
        Test GET /api/v1/videos/:id hides private videos.

        Show only if requester is owner.
        """
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.get(self.uri + str(self.u2.videos[3].id))
        self.assertEqual(r.status_code, 403)

        self.client.credentials(HTTP_AUTHORIZATION=self.auth2, format='json')
        r = self.client.get(self.uri + str(self.u2.videos[3].id))
        self.assertEqual(r.status_code, 200)

    def test_authenticated_user_can_create_own_video(self):
        """Test POST /api/v1/videos/ creates video."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.post(self.uri, {
            'hash': 'morganehash', 'title': 'morgane\'s video 1',
            'original_url': 'http://example.com',
            'embed_url': 'http://example.com',
            'duration': '00:00:01',
            'collection': self.u1.collections.first().id
        })
        self.assertEqual(r.status_code, 201)
        self.assertEqual(len(self.u1.videos), 1)

    def test_authenticated_user_cant_create_video_for_another_user(self):
        """
        Test POST /api/v1/videos/ fails if trying to exploit.

        User can not set owner of video. Calling the endpoint
        always create video for the current user.
        """
        self.assertEqual(len(self.u1.videos), 0)
        self.assertEqual(len(self.u2.videos), 4)
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.post(self.uri, {
            'hash': 'morganehash', 'title': 'morgane\'s video 2',
            'original_url': 'http://example.com',
            'embed_url': 'http://example.com',
            'duration': '00:00:01',
            'collection': self.u2.collections.first().id
        })
        self.assertEqual(r.status_code, 403)
        self.assertEqual(len(self.u1.videos), 0)
        self.assertEqual(len(self.u2.videos), 4)

    def test_authenticated_user_can_delete_own_video(self):
        """Test DELETE /api/v1/videos/ deletes owned collection."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        self.client.post(self.uri, {
            'hash': 'morganehash', 'title': 'morgane\'s video 2',
            'original_url': 'http://example.com',
            'embed_url': 'http://example.com',
            'duration': '00:00:01',
            'collection': self.u1.collections.first().id
        })
        self.assertEqual(len(self.u1.videos), 1)
        r = self.client.delete(self.uri + str(self.u1.videos[0].id))
        self.assertEqual(r.status_code, 204)
        self.assertEqual(len(self.u1.videos), 0)

    def test_authenticated_user_cant_delete_video_for_another_video(self):
        """
        Test DELETE /api/v1/videos/ fails if trying to exploit.

        Should fail when trying to delete another owner's (user).
        """
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        self.assertEqual(len(self.u1.videos), 0)
        self.assertEqual(len(self.u2.videos), 4)
        r = self.client.delete(self.uri + str(self.u2.videos[0].id))
        self.assertEqual(r.status_code, 403)
        self.assertEqual(len(self.u1.videos), 0)
        self.assertEqual(len(self.u2.videos), 4)
