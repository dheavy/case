"""CASE (MyPleasure API) tests for registration."""
from django.test import TestCase
# from rest_framework.test import APIClient


class RegistrationTestCase(TestCase):
    """Test case for registration process."""

    def setUp(self):
        """Set up test."""
        pass

    def test_prerequisites(self):
        """Test prerequisites for suite."""
        pass

    def test_register_fails_if_user_authenticated(self):
        """Test POST /api/v1/register/ fails if user is authenticated."""
        pass

    def test_register_check_username_returns_if_username_exists(self):
        """
        Test GET /api/v1/register/check/username/:username.

        Should return 200 - username is already taken (found).
        """
        pass

    def test_register_check_username_returns_if_username_not_found(self):
        """
        Test GET /api/v1/register/check/username/.

        Should return 404 - username not taken (free to use).
        """
        pass

    def test_register_fails_if_username_missing(self):
        """Test POST /api/v1/register/ fails on missing username."""
        pass

    def test_register_fails_if_password_missing(self):
        """Test POST /api/v1/register/ fails on missing password."""
        pass

    def test_register_fails_if_confirm_password_missing(self):
        """Test POST /api/v1/register/ fails on missing confirm_password."""
        pass

    def test_register_fails_if_password_dont_match(self):
        """Test POST /api/v1/register/ fails on password mismatch."""
        pass

    def test_register_email_not_mandatory(self):
        """Test POST /api/v1/register/ accepts blank email parameter."""
        pass

    def test_register_provides_default_email_if_successful_without_email(self):
        """
        Test POST /api/v1/register/ sets default email if none provided.

        Obviously only working on successful registration.
        """
        pass

    def test_register_creates_new_user_with_matching_credentials(self):
        """
        Test POST /api/v1/register/ create new user.

        Authentication credentials are matching
        those provided during registration.
        """
        pass

    def test_register_creates_user_not_admin_not_staff(self):
        """
        Test POST /api/v1/register/ creates a plain user.

        It's neither an admin nor a staff member.
        """
        pass
