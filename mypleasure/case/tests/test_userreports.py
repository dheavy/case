"""CASE (MyPleasure API) tests for UserReport."""
from django.test import TestCase
from django.contrib.auth import get_user_model
from rest_framework.test import APIClient
from case.models import UserReport, Video


class UserReportTestCase(TestCase):
    """Test case for UserReport model."""

    def setUp(self):
        """Set up test."""
        self.client = APIClient()
        self.u1 = get_user_model().objects.create_user(
            'morgane', 'azertyuiop'
        )
        self.u2 = get_user_model().objects.create_user(
            'marion', 'azertyuiop'
        )
        self.video = Video.objects.create(
            collection=self.u1.collections.first(),
            hash='h1', title='t1', slug='t1', poster='p1',
            original_url='u1', embed_url='e1',
            duration='--:--:--', is_naughty=False
        )
        self.report = UserReport.objects.create(
            video=self.video, reporter=self.u2
        )

    def test_prerequisites(self):
        """Test prerequisite for tests."""
        self.assertEqual(self.u1.videos[0], self.report.video)
