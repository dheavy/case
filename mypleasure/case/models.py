"""CASE (MyPleasure API) models."""
import uuid
from django.db import models
from django.conf import settings
from django.contrib.auth.models import BaseUserManager, AbstractBaseUser
from django.contrib.auth.models import PermissionsMixin
from django.db.models.signals import pre_save, post_save
from django.template.defaultfilters import slugify
from django.dispatch import receiver


class CustomUserManager(BaseUserManager):
    """Manager object for CustomUser."""

    def create_default_collection(self, user):
        """Create default collection upon user creation."""
        user.collections.create(
            name='my collection', is_private=False
        )

    def create_user(self, username, password, email=None):
        """Create user."""
        if not username:
            raise ValueError('Users must have a username.')

        user = self.model(
            username=username,
            email=self.normalize_email(email)
        )

        user.set_password(password)
        user.is_active = True
        user.save()

        self.create_default_collection(user)

        return user

    def create_superuser(self, username, password, email):
        """Create superuser."""
        user = self.create_user(username, password=password, email=email)
        user.is_staff = True
        user.is_superuser = True
        user.save()
        return user


class UserFollowRelationship(models.Model):
    """Model defining `follow` relationship between users via pivot table."""

    follower = models.ForeignKey(
        'CustomUser', related_name='follower', blank=True, null=True
    )
    followed = models.ForeignKey(
        'CustomUser', related_name='followed', blank=True, null=True
    )
    since = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "UserFollowRelationship (id: %s, follower: %s, followed: %s, \
since: %s)" % (self.id, self.follower, self.followed, self.since)
        )

    class Meta:
        """Meta for UserFollowRelationship."""

        verbose_name = "User <-> User (follow) relationship"
        verbose_name_plural = "User <-> User (follow) relationships"


class UserBlockRelationship(models.Model):
    """Model defining `block` relationship between users via pivot table."""

    blocker = models.ForeignKey(
        'CustomUser', related_name='blocker', null=True
    )
    blocked = models.ForeignKey(
        'CustomUser', related_name='blocked', null=True
    )
    since = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "UserBlockRelationship (id: %s, blocker: %s, blocked: %s, \
since: %s)" % (self.id, self.blocker, self.blocked, self.since)
        )

    class Meta:
        """Meta for UserBlockRelationship."""

        verbose_name = "User <-> User (block) relationship"
        verbose_name_plural = "User <-> User (block) relationships"


class UserCollectionFollowRelationship(models.Model):
    """Model defining `follow` relationship between user and a collection."""

    user = models.ForeignKey(
        'CustomUser', related_name='user_following', null=True
    )
    collection = models.ForeignKey(
        'Collection', related_name='collection_followed', null=True
    )
    since = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "UserCollectionFollowRelationship (id: %s, user: %s, \
collection: %s, since: %s)" % (self.id, self.user, self.collection, self.since)
        )

    class Meta:
        """Meta for UserCollectionFollowRelationship."""

        verbose_name = "User <-> Collection (follow) relationship"
        verbose_name_plural = "User <-> Collection (follow) relationships"


class UserCollectionBlockRelationship(models.Model):
    """Model defining `block` relationship between user and a collection."""

    user = models.ForeignKey(
        'CustomUser', related_name='user_blocking', null=True
    )
    collection = models.ForeignKey(
        'Collection', related_name='collection_blocked', null=True
    )
    since = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        """Render string representation of instance."""
        return (
            "UserCollectionBlockRelationship (id: %s, user: %s, \
collection: %s, since: %s)" % (self.id, self.user, self.collection, self.since)
        )

    class Meta:
        """Meta for UserCollectionBlockRelationship."""

        verbose_name = "User <-> Collection (block) relationship"
        verbose_name_plural = "User <-> Collection (block) relationships"


class CustomUser(PermissionsMixin, AbstractBaseUser):
    """Custom User class, enhancing User model."""

    USERNAME_FIELD = 'username'
    REQUIRED_FIELDS = ['email', 'password']

    objects = CustomUserManager()
    username = models.CharField(max_length=40, unique=True, db_index=True)
    email = models.EmailField(max_length=254, unique=True, blank=True)
    last_access = models.DateTimeField(auto_now_add=True)
    date_joined = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True, blank=True)
    is_active = models.BooleanField(default=True)
    is_staff = models.BooleanField(default=False)

    related_users = models.ManyToManyField(
        'self', symmetrical=False, through='UserFollowRelationship'
    )

    related_collections = models.ManyToManyField(
        'Collection', through='UserCollectionFollowRelationship'
    )

    def attach_facebook_account(self, facebook_id):
        """Attach a Facebook account to this user."""
        try:
            FacebookUser.objects.get(user=self).delete()
        except:
            pass
        FacebookUser.objects.create(facebook_id=facebook_id, user=self)

    def follow_user(self, user):
        """Follow a user."""
        try:
            UserFollowRelationship.objects.get(
                follower=self, followed=user
            )
        except:
            relationship = UserFollowRelationship(follower=self, followed=user)
            relationship.save()
        return True

    def unfollow_user(self, user):
        """Unfollow a user."""
        try:
            UserFollowRelationship.objects.get(
                follower=self.id, followed=user.id
            ).delete()
            return True
        except:
            return False

    def block_user(self, user):
        """Block a user."""
        try:
            UserBlockRelationship.objects.get(
                blocker=self, blocked=user
            )
        except:
            relationship = UserBlockRelationship(blocker=self, blocked=user)
            relationship.save()
        return True

    def unblock_user(self, user):
        """Unblock a user."""
        try:
            UserBlockRelationship.objects.get(
                blocker=self.id, blocked=user.id
            ).delete()
        except:
            return False

    def follow_collection(self, collection):
        """Follow another user's collection."""
        try:
            UserCollectionFollowRelationship.objects.get(
                user=self, collection=collection
            )
        except:
            relationship = UserCollectionFollowRelationship(
                user=self, collection=collection
            )
            relationship.save()

    def unfollow_collection(self, collection):
        """Unfollow a collection."""
        try:
            UserCollectionFollowRelationship.objects.get(
                user=self.id, collection=collection.id
            ).delete()
        except:
            pass

    def block_collection(self, collection):
        """Block another user's collection."""
        try:
            UserCollectionBlockRelationship.objects.get(
                user=self, collection=collection
            )
        except:
            relationship = UserCollectionBlockRelationship(
                user=self, collection=collection
            )
            relationship.save()

    def unblock_collection(self, collection):
        """Unblock a collection."""
        try:
            UserCollectionBlockRelationship.objects.get(
                user=self.id, collection=collection.id
            ).delete()
        except:
            pass

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

    def has_video(self, hash=None, url=None, include_queue=True):
        """Tell if user owns a video matching given hash or url."""
        collection = None

        if hash is not None:
            v = Video.objects.filter(hash=hash, collection__owner=self.id)
        elif url is not None:
            v = Video.objects.filter(
                original_url=url, collection__owner=self.id
            )
        if len(v) > 0:
            try:
                collection = Collection.objects.get(pk=v[0].collection.id)
            except:
                pass

        # If required, check in media acquisition queue.
        if include_queue is True:
            if hash is not None:
                m = MediaQueue.objects.filter(hash=hash, requester=self.id)
            elif url is not None:
                m = MediaQueue.objects.filter(url=url, requester=self.id)
            if len(m) > 0:
                m = m[0]
                try:
                    collection = Collection.objects.get(pk=m.collection_id)
                except:
                    pass

        return collection

    def __str__(self):
        """Return string representation of model."""
        return (
            "CustomUser (id: %s, username: %s, \
is_staff: %s, is_superuser: %s)" %
            (
                self.id, self.username, self.is_staff,
                self.is_superuser
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

    @property
    def followers(self):
        """Return list of users following self."""
        relationship = UserFollowRelationship.objects.filter(followed=self.id)
        return [
            r.follower for r in relationship if r.follower.is_active is True
        ]

    @property
    def following(self):
        """Return list of users self is following."""
        relationship = UserFollowRelationship.objects.filter(follower=self.id)
        return [
            r.followed for r in relationship if r.followed.is_active is True
        ]

    @property
    def blocked_by(self):
        """Return list of users blocking self."""
        relationship = UserBlockRelationship.objects.filter(blocked=self.id)
        return [
            r.blocker for r in relationship if r.blocker.is_active is True
        ]

    @property
    def blocking(self):
        """Return list of users self has blocked."""
        relationship = UserBlockRelationship.objects.filter(blocker=self.id)
        return [
            r.blocked for r in relationship if r.blocked.is_active is True
        ]

    @property
    def collections_followed(self):
        """
        Return list of collections self is following.

        Include collections for followed users, if not inadvertently
        blocking user or collection as well.
        """
        relationship = UserCollectionFollowRelationship.objects.filter(
            user=self.id
        )

        followed_collections = [
            r.collection for r in relationship
            if r.collection.owner.is_active is True
        ]

        followed_collections += [
            c for u in self.following
            for c in u.collections.all()
            if u not in self.blocking and
            u.collections not in self.collections_blocked and
            u.collections not in followed_collections
        ]

        return followed_collections

    @property
    def collections_blocked(self):
        """
        Return list of collections self has blocked.

        Include collections from blocked users.
        """
        blocking = self.blocking
        r = UserCollectionBlockRelationship.objects.filter(user=self.id)
        c = [
            blocked.collection for blocked in r
            if blocked.collection.owner and
            blocked.collection.owner.is_active is True
        ]
        u = [
            col for user in blocking for col in user.collections.all()
            if user.is_active is True
        ]
        return list(set(c + u))

    class Meta:
        """Normalize CustomUser name to "User" in admin panel."""

        verbose_name = 'User'
        verbose_name_plural = 'Users'


class FacebookUser(models.Model):
    """
    FacebookUser.

    Attached to a CustomUser, bears the possible related Facebook identity.
    Primarily used to log in a user using Facebook Login.
    """

    facebook_id = models.CharField(max_length=50)
    user = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='facebook',
        blank=True, null=True
    )

    def __str__(self):
        """Return string representation of model."""
        return (
            "FacebookUser (id: %s, facebook_id: %s, user: %s)" %
            (
                self.id, self.facebook_id, self.user
            )
        )

    class Meta:
        """Normalize name in admin panel."""

        verbose_name = 'Related Facebook user'
        verbose_name_plural = 'Related Facebook users'


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
        null=True,
        on_delete=models.CASCADE
    )
    name = models.CharField(max_length=30)
    slug = models.SlugField(max_length=30, blank=True)
    is_private = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    related_users = models.ManyToManyField(
        CustomUser, through=UserCollectionFollowRelationship
    )

    @property
    def followers(self):
        """Return list of users following self."""
        pass

    @property
    def blocked_by(self):
        """Return list of users blocking self."""
        pass

    @property
    def is_default(self):
        """Whether it's default (first) collection assigned to CustomUser."""
        return self.id == self.owner.collections.first().id

    def __str__(self):
        """Render string representation of instance."""
        return (
            "Collection (id: %s, owner: %s, name: %s, slug: %s, private: %s)" %
            (
                self.id, (self.owner and self.owner.id or None),
                self.name, self.slug, self.is_private
            )
        )


class VideoManager(models.Manager):
    """Manager for Video model."""

    @property
    def scales_for_form(self):
        """Return available scales for form select in admin."""
        return tuple(
            [(e, e) for e in ('normal', 'large',)]
        )


class Video(models.Model):
    """
    Video.

    'Belongs To' one Collection.
    'Has Many' and 'Belongs To Many' Tags.
    """

    objects = VideoManager()

    collection = models.ForeignKey(
        Collection, related_name='videos', on_delete=models.CASCADE
    )
    tags = models.ManyToManyField(
        'Tag', blank=True, related_name='videos'
    )
    hash = models.CharField(max_length=100, db_index=True)
    title = models.CharField(max_length=100)
    slug = models.SlugField(max_length=100, blank=True)
    poster = models.CharField(max_length=100, null=True, blank=True)
    original_url = models.URLField(max_length=100)
    embed_url = models.URLField(max_length=100)
    duration = models.CharField(max_length=8, default='--:--:--')
    is_naughty = models.BooleanField(default=False)
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    # Scale of the video. Consider it an enum:
    # - "normal" (default)
    # - "large"
    scale = models.CharField(max_length=20, default='normal')

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
            "Video (id: %s, collection_id: %s, title: %s, slug: %s,\
poster: %s original_url: %s, embed_url: %s, scale: %s, duration: %s, \
is_naughty: %s, hash: %s)" %
            (
                self.id, self.collection.id, self.title, self.slug,
                self.poster, self.original_url, self.embed_url,
                self.scale, self.duration, self.is_naughty, self.hash
            )
        )


class Tag(models.Model):
    """
    Tag.

    'Has Many' and 'Belongs To Many' Videos.
    """

    name = models.CharField(max_length=20, unique=True)
    slug = models.SlugField(max_length=20, blank=True)
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
    claimed_at = models.DateTimeField(blank=True, null=True)

    def __str__(self):
        """Return string representation of model."""
        return (
            "Invite (email: %s, code: %s, sender: %s)" %
            (
                self.email, self.code, self.user_created
            )
        )


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
    status = models.CharField(max_length=30, blank=True, default='pending')
    created_at = models.DateTimeField(auto_now_add=True)

    def __str__(self):
        """Return string representation of model."""
        return (
            "MediaQueue (id: %s, hash: %s, url: %s, \
requester: %s, collection_id: %s, status: %s)" %
            (
                self.id, self.hash, self.url, self.requester,
                self.collection_id, self.status
            )
        )

    class Meta:
        """Use legacy name for this table."""

        db_table = 'mediaqueue'

        verbose_name = 'Queued video'
        verbose_name_plural = 'Queued videos'


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
    created_at = models.DateTimeField(auto_now_add=True)

    def as_video_params(self):
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

    def __str__(self):
        """Return string representation of model."""
        return (
            "MediaStore (id: %s, hash: %s, original_url: %s, \
embed_url: %s, poster: %s, duration: %s, naughty: %s)" %
            (
                self.id, self.hash, self.original_url, self.embed_url,
                self.poster, self.duration, self.naughty
            )
        )

    class Meta:
        """Use legacy name for this table."""

        db_table = 'mediastore'

        verbose_name = 'Stored video'
        verbose_name_plural = 'Stored videos'


class UserReportManager(models.Manager):
    """Manager for UserReport model."""

    @property
    def statuses_for_form(self):
        """Return available statuses for form select."""
        return tuple(
            [(e, e) for e in ('new', 'reviewing', 'accepted', 'dismissed',)]
        )


class UserReport(models.Model):
    """
    User report.

    Model dealing with a media reported on site by a user.
    Staff should inquire upon such a report, to see if a media
    actually fits the site's guideline.

    `assignee` references the staff member who is assigned (either by herself
    or was assigned by Admin) to review the report.
    `reporter` references user who made the report. `comments` should hold
    reviewer's optional notes in Admin. `status` should be set in Admin in
    such way that it should be considered as an enum such as follow:
    - 'new' is the state given by default for any new report,
    - 'reviewing' is the state set by a staff member when reviewing the report,
    - 'accepted' is set by reviewer when inquiry validates report, and video is
      effectively being removed,
    - 'dismissed' is set by reviewer after inquiry, to ignore report.
    """

    objects = UserReportManager()

    video = models.ForeignKey(
        Video, related_name='reports', on_delete=models.CASCADE
    )
    reporter = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        related_name='reporter'
    )
    assignee = models.OneToOneField(
        settings.AUTH_USER_MODEL,
        on_delete=models.CASCADE,
        blank=True, null=True,
        related_name='assignee'
    )
    comments = models.TextField(blank=True, null=True)
    status = models.CharField(max_length=20, blank=True, default="new")
    created_at = models.DateTimeField(auto_now_add=True)
    updated_at = models.DateTimeField(auto_now=True)

    def __str__(self):
        """Return string representation of model."""
        return (
            "UserReport (id: %s, video: %s, reporter: %s, assignee: %s \
comments: %s, created_at: %s, updated_at: %s)" %
            (
                self.id, self.video, self.reporter, self.assignee,
                self.comments, self.created_at, self.updated_at
            )
        )

    class Meta:
        """Normalize name in admin panel."""

        verbose_name = 'Report (from user on site)'
        verbose_name_plural = 'Reports (from user on site)'


class Message(models.Model):
    """
    Message.

    Primarily used as notification system from admins to users.
    Might later be expended as a full-fledged messaging system.
    Status is considered an enum with the following values:
    - 'new'
    - 'read'
    - 'deleted'
    """

    sender = models.ForeignKey(
        CustomUser, related_name='messages_sent', on_delete=models.CASCADE
    )
    recipient = models.ForeignKey(
        CustomUser, related_name='messages_received', on_delete=models.CASCADE
    )
    title = models.CharField(max_length=100, null=True, blank=True)
    body = models.TextField(blank=True, null=True)
    status = models.CharField(max_length=20, blank=True, default="new")
    created_at = models.DateTimeField(auto_now_add=True)
    read_at = models.DateTimeField(blank=True, null=True)

    def __str__(self):
        """Return string representation of model."""
        return (
            "Message (from: %s, to: %s, status: %s, created_at: %s, \
read_at: %s)" %
            (
                self.id, self.sender.username, self.recipient.username,
                self.status, self.created_at, self.read_at
            )
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


def set_no_email_suffix(instance):
    """Generate dummy email stating the User has not provided an email."""
    return instance.username + '.no.email.provided@mypleasu.re'


@receiver(pre_save, sender=CustomUser)
def set_default_email(sender, instance, *args, **kwargs):
    """Set default email address when a User did not provide it."""
    if instance.email == '' or instance.email is None:
        instance.email = set_no_email_suffix(instance)


@receiver(pre_save, sender=Collection)
def set_default_collection_name(sender, instance, *args, **kwargs):
    """Set default collection name if none provided."""
    if instance.name == '' or instance.name is None:
        instance.name = 'my collection'


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


@receiver(post_save, sender=UserReport)
def notify_on_new_report(sender, instance, *args, **kwargs):
    """Notify of a new User report."""
    # TODO: Use logger to notify on Slack/Trello/email
    # if instance.status === 'new'
    pass
