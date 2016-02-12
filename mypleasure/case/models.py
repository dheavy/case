"""CASE (MyPleasure API) models."""
from django.db import models
from django.contrib.auth.models import User


class Collection(models.Model):
    """
    Collection (of Videos).

    'Belongs To' one User.
    'Has Many' Videos.
    """

    owner = models.ForeignKey(User, on_delete=models.CASCADE)
    name = models.CharField(max_length=30)
    slug = models.CharField(max_length=30)
    is_private = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    def is_default(self):
        """True if it's the default (first) collection assigned to User."""
        return self.id == self.owner.collection_set.first().id


class Video(models.Model):
    """
    Videos.

    'Belongs To' one Collection.
    'Has Many' and 'Belongs To Many' Tags.
    """

    collection = models.ForeignKey('Collection', on_delete=models.CASCADE)
    tags = models.ManyToManyField('Tag')
    hash = models.CharField(max_length=100, db_index=True)
    title = models.CharField(max_length=100)
    slug = models.CharField(max_length=100)
    poster = models.CharField(max_length=100)
    original_url = models.CharField(max_length=100)
    embed_url = models.CharField(max_length=100)
    duration = models.CharField(max_length=8, default='--:--:--')
    is_naughty = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    def get_owner(self):
        """Return User owning the Collection."""
        return self.collection.owner

    def is_private(self):
        """Return privacy status based on the collection it belongs to."""
        return self.collection.is_private

    def __str__(self):
        """Render string representation of instance."""
        return (
            "Video (id: %s, collectionid: %s, title: %s, slug: %s, poster: %s \
            originalurl: %s, embedurl: %s, duration: %s, isnaughty: %s)" %
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

    videos = models.ManyToManyField('Video')
    name = models.CharField(max_length=20)
    slug = models.CharField(max_length=20)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "Tag (id: %s, name: %s, slug: %s)" %
            (self.id, self.name, self.slug)
        )


class Invite(models.Model):
    """
    Invite, sent from a User to a prospect via email.

    'Belongs To' one User.
    """

    sender = models.ForeignKey(User, on_delete=models.CASCADE)


class RememberToken(models.Model):
    """
    Token generated and used when User requires a password reset link.

    Note: it's irrelevent if User did not give her email address.
    """

    user = models.ForeignKey(User, on_delete=models.CASCADE)
    token = models.CharField(max_length=50)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "RememberToken (id: %s, userid: %s, username: %s" %
            (self.id, self.user.id, self.user.username)
        )
