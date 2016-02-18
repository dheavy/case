"""CASE (MyPleasure API) models."""
from django.db import models
from django.conf import settings
from django.contrib.auth.models import BaseUserManager, AbstractBaseUser
from django.contrib.auth.models import PermissionsMixin
from django.db.models.signals import pre_save
from django.template.defaultfilters import slugify
from django.dispatch import receiver


class CustomUserManager(BaseUserManager):
    """Manager object for CustomUser."""

    def create_user(self, username, password, email=None):
        """Create user."""
        if not email:
            raise ValueError('Users must have a username.')

        user = self.model(
            username=username.lower(),
            email=self.normalize_email(email)
        )

        user.set_password(password)
        user.is_active = True
        user.save(using=self._db)
        return user

    def create_superuser(self, username, password, email):
        """Create superuser."""
        user = self.create_user(username, password=password, email=email)
        user.is_staff = True
        user.is_superuser = True
        user.save(using=self._db)
        return user


class CustomUser(PermissionsMixin, AbstractBaseUser):
    """Custom User class, enhancing User model."""

    objects = CustomUserManager()
    username = models.CharField(max_length=40, unique=True, db_index=True)
    email = models.EmailField(max_length=254, unique=True, blank=True)
    last_access = models.DateTimeField(auto_now_add=True)
    date_joined = models.DateTimeField(auto_now_add=True)
    is_active = models.BooleanField(default=True)
    is_staff = models.BooleanField(default=False)

    USERNAME_FIELD = 'username'
    REQUIRED_FIELDS = ['email']

    def get_full_name(self):
        """Return 'short name' representation of model."""
        return self.username

    def get_short_name(self):
        """Return 'short name' representation of model."""
        return self.username

    def __str__(self):
        """Return string representation of model."""
        return self.username

    @property
    def videos(self):
        """
        Aggregate and return User's videos.

        Ensure aggregation is respectful of privacy prerogative.
        """
        return [
            v for c in self.collections.all()
            for v in c.videos.all()
            if (not v.is_private or c.owner.id == self.id)
        ]


class Collection(models.Model):
    """
    Collection (of Videos).

    'Belongs To' one CustomUser.
    'Has Many' Videos.
    """

    owner = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        related_name='collections',
        on_delete=models.CASCADE
    )
    name = models.CharField(max_length=30)
    slug = models.CharField(max_length=30)
    is_private = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    @property
    def is_default(self):
        """Whether it's default (first) collection assigned to CustomUser."""
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
    tags = models.ManyToManyField(
        'Tag', blank=True, related_name='videos'
    )
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
        u = self.collection.owner
        return {
            'username': u.username, 'email': u.email, 'is_staff': u.is_staff,
            'is_superuser': u.is_superuser, 'last_login': u.last_login,
            'id': u.id
        }

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

    'Belongs To' one CustomUser.
    """

    sender = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        related_name='sender',
        on_delete=models.CASCADE, null=True
    )
    email = models.CharField(max_length=50, null=True)
    code = models.CharField(max_length=100, null=True)
    user_created = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        related_name='user_created',
        on_delete=models.CASCADE, null=True
    )
    created_at = models.DateTimeField(auto_now_add=True)
    claimed_at = models.DateTimeField(auto_now=True)


class RememberToken(models.Model):
    """
    Token generated and used when User requires a password reset link.

    Note: it's irrelevent if User did not give her email address.
    """

    user = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE
    )
    token = models.CharField(max_length=50)
    created_at = models.DateTimeField(auto_now_add=True)
    claimed_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "RememberToken (id: %s, userid: %s, username: %s)" %
            (self.id, self.user.id, self.user.username)
        )


@receiver(pre_save, sender=Collection)
@receiver(pre_save, sender=Video)
@receiver(pre_save, sender=Tag)
def slugify_title_or_name(sender, instance, *args, **kwargs):
    """Slugify name/title before saving a model instance."""
    try:
        instance.slug = slugify(instance.title)
    except:
        pass

    try:
        instance.slug = slugify(instance.name)
    except:
        pass


@receiver(pre_save, sender=CustomUser)
def set_default_email(sender, instance, *args, **kwargs):
    """Set default email address when a User did not provide it."""
    if instance.email == '' or instance.email is None:
        instance.email = instance.username + '.no.email.provided@mypleasu.re'
