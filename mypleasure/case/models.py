"""CASE (MyPleasure API) models."""
import uuid
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
    updated_at = models.DateTimeField(auto_now=True, blank=True)
    is_active = models.BooleanField(default=True)
    is_staff = models.BooleanField(default=False)

    USERNAME_FIELD = 'username'
    REQUIRED_FIELDS = ['email', 'password']

    def disable_account(self):
        """Disable User account."""
        self.is_active = False
        self.email = "%s.user.inactive@mypleasu.re" % (self.username)
        self.save()

    def get_full_name(self):
        """Return 'short name' representation of model."""
        return self.username

    def get_short_name(self):
        """Return 'short name' representation of model."""
        return self.username

    def has_video(self, hash=None, include_queue=True):
        """Tell if user owns a video matching given hash."""
        try:
            Video.objects.get(hash=hash, collection__owner=self.id)
        except:
            return False

        # If required, check in media acquisition queue.
        if include_queue is True:
            try:
                MediaQueue.objects.get(hash=hash, requester=self.id)
            except:
                return False

        return True

    def __str__(self):
        """Return string representation of model."""
        return (
            "CustomUser (id: %s, username: %s, staff: %s, superuser: %s)" %
            (
                self.id, self.username, self.is_staff, self.is_superuser
            )
        )

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

    class Meta:
        """Normalize CustomUser name to "User" in admin panel."""

        verbose_name = 'User'
        verbose_name_plural = 'Users'


class Collection(models.Model):
    """
    Collection (of Videos).

    'Belongs To' one CustomUser.
    'Has Many' Videos.
    """

    owner = models.ForeignKey(
        settings.AUTH_USER_MODEL,
        related_name='collections',
        blank=True,
        on_delete=models.CASCADE
    )
    name = models.CharField(max_length=30)
    slug = models.CharField(max_length=30, blank=True)
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
    slug = models.CharField(max_length=100, blank=True)
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

    name = models.CharField(max_length=20, unique=True)
    slug = models.CharField(max_length=20, blank=True)
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


class MediaQueue(models.Model):
    """
    Media queue element.

    An independant model describing a set of video metada sent by KIPP
    (or CASE, via some 'add new video via page url' command) to be processed
    by TARS. These models represent the video marked as 'pending' in a user's
    video list.
    Though `requester` and `collection_id` are respectively a reference to
    `id` fields in CustomUser and `Collection`, the relationship is not
    enforced.
    Historically, this and MediaStore were using a different DB (Mongo),
    and both CASE and TARS were (and still are) using these data extensively.
    For pragmatic reasons (i.e. $$$) these DB were reconciliated into PSQL.
    MediaQueue exists as a Django model, among other reasons, to benefit from
    the migration system.
    """

    hash = models.CharField(max_length=255, db_index=True)
    url = models.CharField(max_length=255)
    requester = models.IntegerField()
    collection_id = models.IntegerField()
    status = models.CharField(max_length=30, default='pending')
    created_at = models.DateTimeField()

    class Meta:
        """Use legacy name for this table."""

        db_table = 'mediaqueue'


class MediaStore(models.Model):
    """
    Media store element.

    The media store keeps metadata/reference of video previously analyzed.
    TARS checks against it whenever it starts a job to see if it can just
    return the existing reference instead of doing a full job.
    The same historical context for MediaQueue table apply to this one.
    """

    hash = models.CharField(max_length=255, db_index=True)
    title = models.CharField(max_length=255)
    original_url = models.CharField(max_length=255)
    embed_url = models.CharField(max_length=255)
    poster = models.CharField(max_length=255)
    duration = models.CharField(max_length=8)
    naughty = models.BooleanField()
    created_at = models.DateTimeField()

    def render_as_video_params(self):
        """Return parameters for creating Video from this instance."""
        return {
            'hash': self.hash,
            'title': self.title,
            'original_url': self.original_url,
            'embed_url': self.embed_url,
            'poster': self.poster,
            'duration': self.duration,
            'is_naughty': self.naughty,
        }

    class Meta:
        """Use legacy name for this table."""

        db_table = 'mediastore'


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


def set_no_email_suffix(instance):
    """Generate dummy email stating the User has not provided an email."""
    return instance.username + '.no.email.provided@mypleasu.re'


@receiver(pre_save, sender=CustomUser)
def set_default_email(sender, instance, *args, **kwargs):
    """Set default email address when a User did not provide it."""
    if instance.email == '' or instance.email is None:
        instance.email = set_no_email_suffix(instance)


@receiver(pre_save, sender=Tag)
def lowercase_name(sender, instance, *args, **kwargs):
    """Set Tag name to lower case."""
    instance.name = instance.name.lower()


@receiver(pre_save, sender=CustomUser)
def obfuscate_email_if_deactivated(sender, instance, *args, **kwargs):
    """Obfuscate a User's email if User is being deactivated."""
    suffix = '.user.deactivated@mypleasu.re'
    if not instance.is_active and instance.email.find(suffix) == -1:
        instance.email = str(uuid.uuid4()) + suffix
    if instance.is_active and instance.email.find(suffix) > -1:
        instance.email = set_no_email_suffix(instance)
