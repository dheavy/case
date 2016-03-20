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

        response = self.client.post('/api/v1/auth/login/', {
            'username': 'morgane', 'password': 'azertyuiop'
        })
        self.token = response.data['token']
        self.auth = 'Bearer {0}'.format(self.token)

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
        pass

    def test_collection_detail_hides_private_collection_if_not_owner(self):
        """
        Test GET /api/v1/collections/:id hides private collection.

        Show it only if requester is owner.
        """
        pass

    def test_authenticated_user_can_create_own_collection(self):
        """Test POST /api/v1/collections/ creates owned collection."""
        pass

    def test_authenticated_user_cant_create_collection_for_another_user(self):
        """
        Test POST /api/v1/collections/ fails if trying to exploit.

        Should fail when trying to set another owner (user).
        """
        pass

    def test_authenticated_user_can_update_own_collection(self):
        """Test PUT /api/v1/collections/ creates owned collection."""
        pass

    def test_authenticated_user_cant_update_collection_for_another_user(self):
        """
        Test PUT /api/v1/collections/ fails if trying to exploit.

        Should fail when trying to set another owner (user).
        """
        pass

    def test_authenticated_user_can_delete_own_collection(self):
        """Test DELETE /api/v1/collections/ creates owned collection."""
        pass

    def test_authenticated_user_cant_delete_collection_for_another_user(self):
        """
        Test DELETE /api/v1/collections/ fails if trying to exploit.

        Should fail when trying to set another owner (user).
        """
        pass
