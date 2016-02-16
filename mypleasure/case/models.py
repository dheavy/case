"""CASE (MyPleasure API) models."""
from django.db import models
from django.contrib.auth.models import User


class Collection(models.Model):
    """
    Collection (of Videos).

    'Belongs To' one User.
    'Has Many' Videos.
    """

    owner = models.ForeignKey(
        User, related_name='collections', on_delete=models.CASCADE
    )
    name = models.CharField(max_length=30)
    slug = models.CharField(max_length=30)
    is_private = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    @property
    def is_default(self):
        """True if it's the default (first) collection assigned to User."""
        return self.id == self.owner.collections.first().id

    def __str__(self):
        """Render string representation of instance."""
        return (
            "Collection (id: %s, owner: %s, name: %s, slug: %s, private: %s)" %
            (
                self.id, self.owner.id, self.name, self.slug, self.is_private
            )
        )


class Video(models.Model):
    """
    Videos.

    'Belongs To' one Collection.
    'Has Many' and 'Belongs To Many' Tags.
    """

    collection = models.ForeignKey(
        Collection, related_name='videos', on_delete=models.CASCADE
    )
    tags = models.ManyToManyField('Tag', blank=True)
    hash = models.CharField(max_length=100, db_index=True)
    title = models.CharField(max_length=100)
    slug = models.CharField(max_length=100)
    poster = models.CharField(max_length=100, null=True, blank=True)
    original_url = models.CharField(max_length=100)
    embed_url = models.CharField(max_length=100)
    duration = models.CharField(max_length=8, default='--:--:--')
    is_naughty = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    @property
    def owner(self):
        """Return User owning the Collection."""
        return self.collection.owner

    @property
    def is_private(self):
        """Return privacy status based on the collection it belongs to."""
        return self.collection.is_private

    def __str__(self):
        """Render string representation of instance."""
        return (
            "Video (id: %s, collectionid: %s, title: %s, slug: %s, poster: %s \
            originalurl: %s, embedurl: %s, duration: %s, isnaughty: %s))" %
            (
                self.id, self.collection.id, self.title, self.slug,
                self.poster, self.original_url, self.embed_url,
                self.duration, self.is_naughty
            )
        )


class Tag(models.Model):
    """
    Tag.

    'Has Many' and 'Belongs To Many' Videos.
    """

    videos = models.ManyToManyField('Video', blank=True)
    name = models.CharField(max_length=20)
    slug = models.CharField(max_length=20)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "Tag (id: %s, name: %s, slug: %s))" %
            (self.id, self.name, self.slug)
        )


class Invite(models.Model):
    """
    Invite, sent from a User to a prospect via email.

    'Belongs To' one User.
    """

    sender = models.ForeignKey(
        User, related_name='sender', on_delete=models.CASCADE, null=True)
    email = models.CharField(max_length=50, null=True)
    code = models.CharField(max_length=100, null=True)
    user_created = models.ForeignKey(
        User, related_name='user_created', on_delete=models.CASCADE, null=True)
    created_at = models.DateTimeField(auto_now_add=True)
    claimed_at = models.DateTimeField(auto_now=True)


class RememberToken(models.Model):
    """
    Token generated and used when User requires a password reset link.

    Note: it's irrelevent if User did not give her email address.
    """

    user = models.ForeignKey(User, on_delete=models.CASCADE)
    token = models.CharField(max_length=50)
    created_at = models.DateTimeField(auto_now_add=True)
    claimed_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "RememberToken (id: %s, userid: %s, username: %s)" %
            (self.id, self.user.id, self.user.username)
        )
