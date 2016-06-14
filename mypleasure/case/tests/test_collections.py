"""CASE (MyPleasure API) tests for Collection."""
from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient
from case.models import Collection


class CollectionTestCase(TestCase):
    """Test case for Collection model."""

    def setUp(self):
        """Set up test."""
        self.client = APIClient()
        self.u1 = get_user_model().objects.create_user(
            'morgane', 'azertyuiop'
        )
        self.u2 = get_user_model().objects.create_user(
            'marion', 'azertyuiop'
        )
        self.uri = '/api/v1/collections/'

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

    def test_collection_list_requires_authentication(self):
        """Test /api/v1/collections/ requires authentication."""
        r = self.client.get(self.uri)
        self.assertEqual(r.status_code, 403)

    def test_collection_detail_requires_authentication(self):
        """Test /api/v1/collections/:id requires authentication."""
        cid = str(Collection.objects.first().id)
        r = self.client.get(self.uri + cid)
        self.assertEqual(r.status_code, 403)

    def test_default_collection_created_on_user_creation(self):
        """Test new user comes with default collection."""
        self.assertEqual(Collection.objects.all().count(), 2)

    def test_collection_list_hides_private_collection_if_not_owner(self):
        """Test GET /api/v1/collections/ hides private collection.

        Show it only if requester is owner.
        """
        self.u2.collections.create(name='private collection', is_private=True)
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.get(self.uri)
        self.assertEqual(len(r.data['results']), 2)

        self.client.credentials(HTTP_AUTHORIZATION=self.auth2, format='json')
        r = self.client.get(self.uri)
        self.assertEqual(len(r.data['results']), 3)

    def test_collection_detail_hides_private_collection_if_not_owner(self):
        """
        Test GET /api/v1/collections/:id hides private collection.

        Show it only if requester is owner.
        """
        self.u2.collections.create(name='private collection', is_private=True)
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.get(
            self.uri +
            str(self.u2.collections.get(name='private collection').id)
        )
        self.assertEqual(r.status_code, 403)

        self.client.credentials(HTTP_AUTHORIZATION=self.auth2, format='json')
        r = self.client.get(
            self.uri +
            str(self.u2.collections.get(name='private collection').id)
        )
        self.assertEqual(r.status_code, 200)

    def test_authenticated_user_can_create_own_collection(self):
        """Test POST /api/v1/collections/ creates owned collection."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.post(self.uri, {'name': 'my new collection'})
        self.assertEqual(r.status_code, 201)
        self.assertEqual(self.u1.collections.all().count(), 2)

    def test_authenticated_user_cant_create_collection_for_another_user(self):
        """
        Test POST /api/v1/collections/ fails if trying to exploit.

        User can not set owner of collection. Calling the endpoint
        always create collection for the current user.
        """
        self.assertEqual(self.u1.collections.all().count(), 1)
        self.assertEqual(self.u2.collections.all().count(), 1)
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        self.client.post(self.uri, {
            'name': 'my new collection',
            'owner': self.u2.id
        })
        self.assertEqual(self.u1.collections.all().count(), 2)
        self.assertEqual(self.u2.collections.all().count(), 1)

    def test_authenticated_user_can_update_own_collection(self):
        """Test PATCH /api/v1/collections/ updates owned collection."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        self.client.post(self.uri, {'name': 'my new collection'})
        cid = Collection.objects.get(
            name='my new collection', owner=self.u1.id
        ).id
        r = self.client.patch(
            self.uri + str(cid), {'name': 'my altered collection'}
        )
        self.assertEqual(r.status_code, 200)
        self.assertEqual(
            Collection.objects.get(pk=cid).name, 'my altered collection'
        )

    def test_authenticated_user_cant_update_collection_for_another_user(self):
        """
        Test PATCH /api/v1/collections/ fails if trying to exploit.

        Should fail when trying to set another owner (user).
        """
        self.client.credentials(HTTP_AUTHORIZATION=self.auth2, format='json')
        self.client.post(self.uri, {'name': 'marion\'s collection'})
        cid = Collection.objects.get(
            name='marion\'s collection', owner=self.u2.id
        ).id
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.patch(
            self.uri + str(cid), {'name': 'my altered collection'}
        )
        self.assertEqual(r.status_code, 403)
        self.assertEqual(
            Collection.objects.get(pk=cid).name, 'marion\'s collection'
        )

    def test_authenticated_user_can_delete_own_collection(self):
        """Test DELETE /api/v1/collections/ creates owned collection."""
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        self.client.post(self.uri, {'name': 'my new collection'})
        self.assertEqual(self.u1.collections.all().count(), 2)
        cid = Collection.objects.get(
            name='my new collection', owner=self.u1.id
        ).id
        r = self.client.delete(self.uri + str(cid))
        self.assertEqual(r.status_code, 204)
        self.assertEqual(self.u1.collections.all().count(), 1)

    def test_authenticated_user_cant_delete_collection_for_another_user(self):
        """
        Test DELETE /api/v1/collections/ fails if trying to exploit.

        Should fail when trying to delete another owner's (user).
        """
        self.client.credentials(HTTP_AUTHORIZATION=self.auth2, format='json')
        self.client.post(self.uri, {'name': 'my new collection'})
        self.assertEqual(self.u2.collections.all().count(), 2)
        cid = Collection.objects.get(
            name='my new collection', owner=self.u2.id
        ).id
        self.client.credentials(HTTP_AUTHORIZATION=self.auth1, format='json')
        r = self.client.delete(self.uri + str(cid))
        self.assertEqual(r.status_code, 403)
        self.assertEqual(self.u2.collections.all().count(), 2)
