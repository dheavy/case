"""CASE (MyPleasure API) serializers."""
import crypt
import requests
from django.core.validators import validate_email
from django.core.exceptions import ValidationError
from django.core.validators import URLValidator
from django.utils.encoding import force_text
from django.conf import settings
from django.contrib.auth import get_user_model
from django.utils.http import urlsafe_base64_decode as uid_decoder
from django.template.defaultfilters import slugify
from django.contrib.auth.tokens import default_token_generator
from rest_framework import serializers
from case.models import (
    Collection, Video, CustomUser, Tag, MediaStore, MediaQueue, FacebookUser
)
from .filters import (
    filter_videos_by_ownership_for_privacy,
    filter_videos_for_feed
)
from django.contrib.auth.forms import PasswordResetForm, SetPasswordForm


def tmp_username_from_fb(fb_name, fb_id):
    """Generate temporary username from Facebook info."""
    return slugify(fb_name + '-' + fb_id)


def user_serializer(user, pk=None):
    """Return user serializer matching user's level of power."""
    if user.is_staff:    # Full if user is staff member
        return FullUserSerializer
    elif user.id is pk:  # Basic, with email, if self
        return ProfileUserSerializer
    return BasicUserSerializer


def serialized_user_data(user):
    """Return serialized user data matching user's level of power."""
    return user_serializer(user)(user).data


def validate_user_email_password_data(data):
    """Validate User's email/password data on registration & password reset."""
    email = data.get('email', None)

    if email is not None:
        try:
            validate_email(email)
        except ValidationError:
            raise serializers.ValidationError({'code': 'email_invalid'})

    email_owner = CustomUser.objects.filter(email=email).first()
    if email_owner:
        raise serializers.ValidationError({'code': 'email_in_use'})

    if not data.get('password') or not data.get('confirm_password'):
        raise serializers.ValidationError({'code': 'confirm_password_missing'})

    if data.get('password') != data.get('confirm_password'):
        raise serializers.ValidationError({'code': 'passwords_mismatch'})

    return True


class BasicUserSerializer(serializers.ModelSerializer):
    """Serializer class for User, as seen by regular Users."""

    followers = serializers.PrimaryKeyRelatedField(
        queryset=CustomUser.objects.all(), many=True, required=False
    )

    following = serializers.PrimaryKeyRelatedField(
        queryset=CustomUser.objects.all(), many=True, required=False
    )

    blocking = serializers.PrimaryKeyRelatedField(
        queryset=CustomUser.objects.all(), many=True, required=False
    )

    collections_followed = serializers.PrimaryKeyRelatedField(
        queryset=Collection.objects.all(), many=True, required=False
    )

    collections_blocked = serializers.PrimaryKeyRelatedField(
        queryset=Collection.objects.all(), many=True, required=False
    )

    def get_videos(self, obj):
        """Get filtered list of serialized Videos."""
        return filter_videos_by_ownership_for_privacy(
            self.context['request'], obj,
            VideoSerializer
        )

    def get_collections(self, obj):
        """Get filtered list of serialized Collections."""
        try:
            request = self.context['request']
            return [
                CollectionSerializer(
                    c, context={'request': request}
                ).data for c in obj.collections.all()
                if not (c.is_private and c.owner.id != request.user.id)
            ]
        except:
            return []

    def create(self, validated_data, is_active=True):
        """Create User if validation succeeds."""
        password = validated_data.pop('password', None)
        user = CustomUser.objects.create_user(
            validated_data.get('username'),
            validated_data.get('password'),
            validated_data.get('email')
        )
        user.is_active = is_active
        user.set_password(password)
        user.save()
        return user

    def update(self, instance, validated_data):
        """Process update on User."""
        instance.username = validated_data.pop('username', instance.username)
        instance.email = validated_data.pop('email', instance.email)
        password = validated_data.pop('password', None)
        if password:
            instance.set_password(password)
        instance.save()
        return instance

    class Meta:
        """Meta for BasicUserSerializer."""

        model = CustomUser

        fields = (
            'id', 'username', 'password', 'email', 'last_login', 'last_access',
            'followers', 'following', 'blocking', 'collections_followed',
            'collections_blocked'
        )
        extra_kwargs = {
            'password': {'write_only': True},
            'confirm_password': {'write_only': True},
        }


class VideoUserSerializer(BasicUserSerializer):
    """Serializer to bundle user data in a video."""

    class Meta(BasicUserSerializer.Meta):
        """Meta for VideoUserSerializer."""

        fields = ('id', 'username',)


class ProfileUserSerializer(BasicUserSerializer):
    """Serializer for User used when displaying own profile."""

    class Meta(BasicUserSerializer.Meta):
        """Meta for ProfileUserSerializer."""

        fields = BasicUserSerializer.Meta.fields + ('email',)


class FullUserSerializer(BasicUserSerializer):
    """Serializer for User model as seen by staff members."""

    class Meta(BasicUserSerializer.Meta):
        """Meta for FullUserSerializer."""

        fields = BasicUserSerializer.Meta.fields + (
            'email', 'is_staff', 'is_active', 'is_superuser', 'date_joined'
        )


class EditPasswordSerializer(serializers.Serializer):
    """Serializer for direct edit of user's password."""

    def validate(self, data):
        """Attempt validating data."""
        current_password = self.initial_data.get('current_password', None)
        password = self.initial_data.get('password', None)
        confirm_password = self.initial_data.get('confirm_password', None)
        id = self.initial_data.get('user_id', None)

        try:
            user = CustomUser.objects.get(pk=int(id))
        except:
            raise serializers.ValidationError(
                {'code': 'user_not_found'}
            )

        if user.check_password(current_password) is False:
            raise serializers.ValidationError(
                {'code': 'password_invalid'}
            )

        if password != confirm_password:
            raise serializers.ValidationError(
                {'code': 'passwords_mismatch'}
            )

        self.user = user
        return self.initial_data

    def save(self):
        """Save user's new password."""
        self.user.set_password(self.validated_data.get('password'))
        self.user.save()


class EditEmailSerializer(serializers.Serializer):
    """Serializer for direct edit of user's email."""

    def validate(self, data):
        """Attempt validating data."""
        email = self.initial_data.get('email', '')

        try:
            user = CustomUser.objects.get(
                pk=self.initial_data.get('user_id', None)
            )
        except:
            raise serializers.ValidationError(
                {'code': 'user_not_found'}
            )

        if email is not '':
            try:
                validate_email(email)
            except ValidationError:
                raise serializers.ValidationError(
                    {'code': 'email_invalid'}
                )

            owner = CustomUser.objects.filter(email=email)
            if owner and len(owner) >= 1 and owner[0].id != user.id:
                raise serializers.ValidationError(
                    {'code': 'email_in_use'}
                )

        self.user = user
        return self.initial_data

    def save(self):
        """Save changes on user's email."""
        self.user.email = self.validated_data.get('email', '')
        self.user.save()


class CollectionSerializer(serializers.ModelSerializer):
    """Serializer for Collection model."""

    owner = serializers.PrimaryKeyRelatedField(
        queryset=CustomUser.objects.all(),
        required=True
    )

    videos = serializers.SerializerMethodField()

    def get_videos(self, obj):
        """Get filtered list of serialized Videos."""
        return filter_videos_by_ownership_for_privacy(
            self.context['request'], obj,
            VideoSerializer
        )

    class Meta:
        """Meta for Collection serializer."""

        model = Collection
        fields = (
            'id', 'name', 'owner', 'slug', 'videos', 'is_private',
            'is_default', 'created_at', 'updated_at'
        )


class VideoSerializer(serializers.ModelSerializer):
    """Serializer for Video model."""

    owner = serializers.SerializerMethodField()
    tags = serializers.PrimaryKeyRelatedField(
        queryset=Tag.objects.all(), many=True
    )

    def get_owner(self, obj):
        """Returned serialized owner of the Video."""
        return VideoUserSerializer(obj.collection.owner).data

    def validate(self, data):
        """
        Validate instance's data.

        - `id` should refer to an existing Video belonging to user.
        - `title` should exist and be a string under 200 characters;
        - `new_collection_name` if it exists, should override `collection`,
          and be a string between 1 and 200 characters; it should be used
          to create a new collection holding the video;
        - `collection` should exist and refer to the integer ID of a collection
          owned by user.
        """
        id = self.initial_data.get('id', None)
        title = self.initial_data.get('title', None)
        collection = self.initial_data.get('collection', None)
        new_collection_name = self.initial_data.get(
            'new_collection_name', None
        )

        self.video = None

        # Check ID.
        try:
            self.video = Video.objects.get(pk=int(id))
            self.video.owner == self.context['user']
        except:
            raise ValidationError({'code': 'invalid_id'})

        # Check title if exists.
        if title and (len(title) < 1 or len(title) > 200):
            raise ValidationError({'code': 'invalid_title'})

        # Check new collection name and create it, if need be.
        if new_collection_name:
            if (
                type(new_collection_name) is not str and
                len(new_collection_name) < 1 and
                len(new_collection_name) > 200
            ):
                raise ValidationError({'code': 'invalid_new_collection_name'})
            else:
                new_collection = self.context['user'].collections.create(
                    name=self.initial_data['new_collection_name'],
                    is_private=False
                )
                self.initial_data.update({'collection': new_collection})

        self.initial_data.pop('new_collection_name')

        # Check collection ID if exists.
        if collection:
            try:
                c = Collection.objects.get(pk=int(collection))
                assert c.owner == self.context['user']
            except:
                raise ValidationError({'code': 'invalid_collection_id'})

        return data

    def save(self):
        """Update instance."""
        if self.validated_data.get('title'):
            self.video.title = self.validated_data.get('title')

        if self.validated_data.get('collection'):
            self.video.collection = self.validated_data.get('collection')

        self.video.save()

    class Meta:
        """Meta for Video serializer."""

        model = Video
        fields = (
            'id', 'title', 'hash', 'slug', 'poster', 'original_url',
            'embed_url', 'duration', 'is_naughty', 'created_at', 'tags',
            'updated_at', 'scale', 'collection', 'owner', 'is_private',
        )


class FeedNormalSerializer(serializers.Serializer):
    """
    Serializer for the normal Feed - a full list of videos.

    Provides a list of normal videos devoid of reference to collection/owner
    for private videos.
    """

    videos = serializers.SerializerMethodField()

    def get_videos(self, obj):
        """Get videos filtered for Feed."""
        return self.initial_data
        # return filter_videos_for_feed(
        #     self.context['request'],
        #     Video.objects.filter(is_naughty=False),
        #     FeedPublicVideoSerializer, FeedPrivateVideoSerializer
        # )

    def validate(self, data):
        """Validate data."""
        return self.initial_data


class FeedNaughtySerializer(serializers.Serializer):
    """
    Serializer for the naughty Feed - a full list of videos.

    Provides a list of naughty videos devoid of reference to collection/owner
    for private videos.
    """

    videos = serializers.SerializerMethodField()

    def get_videos(self, obj):
        """Get videos filtered for Feed."""
        return filter_videos_for_feed(
            self.context['request'],
            Video.objects.filter(is_naughty=True),
            FeedPublicVideoSerializer, FeedPrivateVideoSerializer
        )


class FeedPrivateVideoSerializer(VideoSerializer):
    """Serializer for *single* video, in Feed, without owner info."""

    class Meta(VideoSerializer.Meta):
        """Meta for FeedPrivateVideoSerializer."""

        fields = (
            'id', 'title', 'hash', 'slug', 'poster', 'original_url',
            'embed_url', 'scale', 'duration', 'is_naughty', 'tags'
        )


class FeedPublicVideoSerializer(FeedPrivateVideoSerializer):
    """Serializer for *single* video, in Feed with owner info."""

    class Meta(FeedPrivateVideoSerializer.Meta):
        """Meta for FeedPublicVideoSerializer."""

        fields = FeedPrivateVideoSerializer.Meta.fields + ('owner',)


class TagSerializer(serializers.ModelSerializer):
    """Serializer for Tag model."""

    videos = serializers.SerializerMethodField()

    def get_videos(self, obj):
        """Get filtered list of serialized Videos."""
        return filter_videos_by_ownership_for_privacy(
            self.context['request'], obj, VideoSerializer
        )

    class Meta:
        """Meta for Tag serializer."""

        model = Tag
        fields = ('id', 'name', 'videos', 'slug')


class UserRegistrationSerializer(serializers.Serializer):
    """
    Serializer used for registering process.

    It does not create the user (that's left to the BasicUserSerializer).
    It just validates the necessary data beforehand.
    """

    username = serializers.CharField(min_length=2, max_length=40)
    email = serializers.EmailField(required=False)
    password = serializers.CharField(min_length=8)
    confirm_password = serializers.CharField(min_length=8)

    def validate(self, data):
        """
        Validate incoming data.

        Email uniqueness if provided, password and confirmation.
        Don't check the first part if we're completing a FB registration.
        """
        if 'facebook' not in self.context:
            if type(self.context['request'].user) is get_user_model():
                raise serializers.ValidationError({
                    'code': 'auth_forbidden'
                })

        if validate_user_email_password_data(data):
            return data

    class Meta:
        """Meta for Tag serializer."""

        fields = ('username', 'email', 'password', 'confirm_password')


class CheckUsernameSerializer(serializers.Serializer):
    """Serializer for 'check username' endpoint."""

    username = serializers.CharField()

    class Meta:
        """Meta for CheckUsernameSerializer."""

        fields = ('username',)


class PasswordResetSerializer(serializers.Serializer):
    """Serializer for requesting a password reset e-mail."""

    email = serializers.EmailField()
    password_reset_form_class = PasswordResetForm

    def validate_email(self, value):
        """Create PasswordResetForm with the serializer."""
        self.reset_form = self.password_reset_form_class(
            data=self.initial_data
        )
        if not self.reset_form.is_valid():
            raise serializers.ValidationError(self.reset_form.errors)

        return value


class PasswordResetConfirmSerializer(serializers.Serializer):
    """Serializer for requesting a password reset e-mail."""

    new_password1 = serializers.CharField(max_length=128)
    new_password2 = serializers.CharField(max_length=128)

    uid = serializers.CharField(required=True)
    token = serializers.CharField(required=True)

    set_password_form_class = SetPasswordForm

    def validate(self, data):
        """Attempt validation."""
        try:
            uid = force_text(uid_decoder(data['uid']))
            self.user = CustomUser.objects.get(pk=uid)
        except (TypeError, ValueError, OverflowError, CustomUser.DoesNotExist):
            raise serializers.ValidationError({'code': 'invalid_uid'})

        # Construct SetPasswordForm instance
        self.set_password_form = self.set_password_form_class(
            user=self.user, data=data
        )
        if not self.set_password_form.is_valid():
            raise serializers.ValidationError(self.set_password_form.errors)
        if not default_token_generator.check_token(self.user, data['token']):
            raise serializers.ValidationError({'code': 'invalid_token'})

        return data

    def save(self):
        """Save new password."""
        self.set_password_form.save()


class CuratedMediaAcquisitionSerializer(serializers.Serializer):
    """Serializer used when performing a media acquisition."""

    strategy = None
    stored_video = None
    target_collection = None

    STRATEGY_STORE = 'store'
    STRATEGY_QUEUE = 'queue'

    def create_hash(self, url):
        """Return hash from a given url."""
        return crypt.crypt(url, crypt.METHOD_MD5)

    def normalize_url(self, url):
        """Normalize URL."""
        # TODO: Normalize URL depending on each provider.
        return url

    def define_acquisition_strategy(self, attrs):
        """
        Define the media acquisition strategy.

        Either by copying existing video in media store,
        or adding a new fetching job in media queue.
        """
        if 'hash' in attrs:
            try:
                # Store cached video if it exists, else raise exception.
                self.stored_video = MediaStore.objects.get(hash=attrs['hash'])
                return self.STRATEGY_STORE
            except:
                raise serializers.ValidationError({
                    'code': 'hash_invalid'
                })

        if 'url' in attrs:
            try:
                validator = URLValidator()
                validator(self.normalize_url(attrs['url']))
                return self.STRATEGY_QUEUE
            except ValidationError:
                raise serializers.ValidationError({'code': 'url_invalid'})

        # If no strategy could be defined, take the last strategy as
        # default and consider it a failure. Mark it as such.
        raise serializers.ValidationError({'code': 'url_missing'})

    def validate(self, attrs):
        """Attempt validation of attributes."""
        attrs = self.initial_data

        # Two possibilities (a.k.a. 'strategy').
        #
        # 1. `hash` was passed. Meaning user is trying to copy
        #    an existing, in use video from a feed.
        #    --> Find video in media store and make copy for user.
        #
        # 2. `url` was passed. Meaning user has sent a curation request
        #    for a new video (via KIPP on 'new video' form on site).
        #    --> Create a new job in media queue.
        #
        # While defining strategy, check validity of its possible tenants,
        # i.e. `hash` provides an existing video,  or `url`
        # is a valid URL.
        try:
            # Store strategy if definable, else raise exception.
            self.strategy = self.define_acquisition_strategy(attrs)
        except Exception as e:
            raise e

        # Shortcut for user.
        u = self.context['user']

        # If collection_id is provided, ensure it exists and belongs to user.
        if 'collection_id' in attrs and int(attrs['collection_id']) != -1:
            try:
                # Store collection if it exists, else raise exception.
                self.target_collection = Collection.objects.get(
                    pk=attrs['collection_id']
                )
                assert self.target_collection.owner == u
            except:
                raise serializers.ValidationError({
                    'code': 'collection_id_invalid'
                })
        else:
            try:
                # If not ID provided, a new collection name should have been.
                # If everything is fine, do create the collection for our user.
                name = attrs['new_collection_name']
                assert bool(name) is not None and name != ''
                self.target_collection = Collection.objects.create(
                    name=name, owner=u
                )
            except:
                raise serializers.ValidationError({
                    'code': 'collection_id_or_name_missing'
                })

        # Finish validation if "queue" strategy is chosen.
        if self.strategy is self.STRATEGY_QUEUE:
            # Prevent duplicates.
            url = self.normalize_url(attrs['url'])
            if u.has_video(url=url):
                raise serializers.ValidationError({'code': 'duplicate'})

            # Prevent duplicates in media queue.
            # We could do it from the user.has_video call above,
            # but this provide more granular lookup for relevant
            # error message.
            # TODO: Unify methods of duplicates lookup + errors codes.
            v = MediaQueue.objects.filter(
                url=url,
                requester=attrs['requester'],
                collection_id=attrs['collection_id']
            ).first()
            if v:
                raise serializers.ValidationError({
                    'code': 'video_already_queued'
                })

        return self.initial_data

    def save(self):
        """Save Video in queue, or copy from store if already available."""
        if self.strategy is self.STRATEGY_QUEUE:
            self.add_to_queue(
                hash=crypt.crypt(self.validated_data['url'], crypt.METHOD_MD5),
                url=self.validated_data['url'],
                requester=self.context['user'].id,
                collection_id=self.target_collection,
                title=self.validated_data['title']
            )
            return {'code': 'added'}

        if self.strategy is self.STRATEGY_STORE:
            self.stored_video.copy_as_video(
                Video, self.target_collection, self.validated_data['title']
            )
            return {'code': 'available'}

    def add_to_queue(self, hash, url, requester, collection_id, title):
        """Add job to media queue."""
        MediaQueue.objects.create(
            hash=hash, url=url, requester=requester,
            collection_id=collection_id, status='pending',
            title=title
        )


class CuratedMediaFetchSerializer(serializers.Serializer):
    """Serializer for fetching new videos to be displayed."""

    def validate(self, attrs):
        """Validate data."""
        user_id = self.context['request'].user.id
        if 'userid' not in self.initial_data or int(self.initial_data[
            'userid'
        ]) != user_id:
            raise serializers.ValidationError({'code': 'id_user_mismatch'})
        return self.initial_data


class FollowUserSerializer(serializers.Serializer):
    """Serializer for follow user process."""

    def validate(self, attrs):
        """Validate data, make changes."""
        try:
            other_user = CustomUser.objects.get(
                pk=int(self.initial_data.get('pk'))
            )
        except:
            raise serializers.ValidationError({'user': 'Not found.'})

        if self.initial_data.get('intent', None) == 'follow':
            self.initial_data.get('current_user').follow_user(other_user)
        elif self.initial_data.get('intent', None) == 'unfollow':
            # Will return True if unfollowed successful, False otherwise.
            if self.initial_data.get(
                'current_user'
            ).unfollow_user(other_user) is False:
                raise serializers.ValidationError({
                    'follow': 'Unfollow failed.'
                })
        else:
            raise serializers.ValidationError({
                'follow': 'Intent misunderstood.'
            })

        return self.initial_data


class BlockUserSerializer(serializers.Serializer):
    """Serializer for block user process."""

    def validate(self, attrs):
        """Validate data, make changes."""
        try:
            other_user = CustomUser.objects.get(
                pk=int(self.initial_data.get('pk', 0))
            )
        except:
            raise serializers.ValidationError({'code': 'user_not_found'})

        if self.initial_data.get('intent', None) == 'block':
            self.initial_data.get('current_user', None).block_user(other_user)
        elif self.initial_data.get('intent', None) == 'unblock':
            if self.initial_data.get(
                'current_user', None
            ).unblock_user(other_user) is False:
                raise serializers.ValidationError({'code': 'unblock_failed'})
        else:
            raise serializers.ValidationError({'code': 'intent_misunderstood'})

        return self.initial_data


class FollowCollectionSerializer(serializers.Serializer):
    """Serializer for follow collection process."""

    def validate(self, attrs):
        """Validate data, make changes."""
        try:
            c = Collection.objects.get(
                pk=int(self.initial_data.get('pk', 0))
            )
        except:
            raise serializers.ValidationError({'code': 'collection_not_found'})

        if self.initial_data.get('intent', None) == 'follow':
            self.initial_data.get('current_user', None).follow_collection(c)
        elif self.initial_data.get('intent', None) == 'unfollow':
            self.initial_data.get('current_user', None).unfollow_collection(c)
        else:
            raise serializers.ValidationError({'code': 'intent_misunderstood'})

        return self.initial_data


class BlockCollectionSerializer(serializers.Serializer):
    """Serializer for block collection process."""

    def validate(self, attrs):
        """Validate data, make changes."""
        try:
            c = Collection.objects.get(
                pk=int(self.initial_data.get('pk', 0))
            )
        except:
            raise serializers.ValidationError({'code': 'collection_not_found'})

        if self.initial_data.get('intent', None) == 'block':
            self.initial_data.get('current_user', None).block_collection(c)
        elif self.initial_data.get('intent', None) == 'unblock':
            self.initial_data.get('current_user', None).unblock_collection(c)
        else:
            raise serializers.ValidationError({'code': 'intent_misunderstood'})

        return self.initial_data


class FacebookUserSerializer(serializers.Serializer):
    """Serializer to either create or authenticate user from FB data."""

    def validate(self, data):
        """Validate data."""
        # Verify email.
        email = self.initial_data.get('fb_email', None)
        if type(email) is list:
            email = email[0]
        try:
            validate_email(email)
        except ValidationError as e:
            raise serializers.ValidationError({
                'code': 'email_invalid'
            })

        # Verify access token.
        try:
            fb_access_token = self.initial_data.get('fb_access_token', None)

            if fb_access_token is not None:
                fb_id = self.initial_data.get('fb_id', None)
                req_uri = (
                    'https://graph.facebook.com/debug_token?' +
                    'input_token={0}' +
                    '&access_token={1}'
                ).format(fb_access_token, settings.FB_APP_TOKEN)

                req = requests.get(req_uri)
                res = req.json().get('data')

                if res.get('user_id') != fb_id:
                    raise ValidationError({
                        'code': 'invalid_fb_token_through_fb_id'
                    })

                if res.get('is_valid') is not True:
                    raise ValidationError({
                        'code': 'invalid_fb_token_through_valid'
                    })

                if res.get('app_id') != settings.FB_CLIENT_ID:
                    raise ValidationError({
                        'code': 'invalid_fb_token_through_app_id'
                    })

        except ValidationError as e:
            raise e

        return self.initial_data

    def create_or_authenticate(self):
        """Create and/or authenticate user via a Facebook token."""
        fb_id = self.validated_data.get('fb_id')
        fb_name = self.validated_data.get('fb_name')
        fb_email = self.validated_data.get('fb_email')
        fb_access_token = self.validated_data.get('fb_access_token')

        # Return existing elements if user exists from a preceding
        # attempt that had not been completed yet.
        try:
            user = CustomUser.objects.get(
                username=tmp_username_from_fb(fb_name, fb_id)
            )
            serializer = FacebookCreateUserSerializer()
            return serializer.continue_create_from_interrupted(
                user, fb_name, fb_id, fb_email
            )
        except:
            pass

        fb_user_queryset = FacebookUser.objects.filter(facebook_id=fb_id)
        if fb_user_queryset.count() > 0:
            # User exists - authenticate.
            return self.auth(fb_user_queryset[0].user, fb_access_token)
        else:
            # New user - initialize creation.
            # Will return a payload to view that should trigger,
            # in the frontend, the redirection to a new register view
            # where user can complete FB registration (CustomUser model
            # created, username choice pending).
            # See "facebook-register" route.
            serializer = FacebookCreateUserSerializer(
                data={
                    'fb_id': fb_id,
                    'fb_name': fb_name,
                    'fb_email': fb_email,
                    'fb_access_token': fb_access_token
                }
            )
            serializer.is_valid(raise_exception=True)
            return serializer.init_create()

    def auth(self, user, token):
        """Authenticate using Facebook user."""
        serializer = FacebookAuthenticateUserSerializer(data={'user': user})
        serializer.is_valid(raise_exception=True)
        return {'user': user}

    def finish_create(self):
        """
        Proxy for the FacebookCreateUserSerializer method of the same name.

        Engage the finalization of a Facebook-based user account.
        """
        serializer = FacebookCreateUserSerializer(data=self.validated_data)
        serializer.is_valid(raise_exception=True)
        return serializer.finish_create()


class FacebookCreateUserSerializer(serializers.Serializer):
    """Serializer to process creation of a new user from FB data."""

    def validate(self, data):
        """
        Validate data.

        For the first batch (init_create), rely on validation
        from FacebookUserSerializer.

        Implement a custom validation for the
        second batch (finish_create).
        """
        if 'tmp_username' in self.initial_data:
            pwd = crypt.crypt(
                self.initial_data.get('fb_email'),
                crypt.METHOD_MD5
            )
            username_serializer = CheckUsernameSerializer(
                data={'username': self.initial_data.get('username', '')}
            )
            username_serializer.is_valid(raise_exception=True)
            registration_serializer = UserRegistrationSerializer(
                data={
                    'username': self.initial_data.get('username'),
                    'password': pwd,
                    'confirm_password': pwd,
                    'email': self.initial_data.get('fb_email'),
                },
                context={'facebook': True}
            )
            registration_serializer.is_valid(raise_exception=True)

        return self.initial_data

    def continue_create_from_interrupted(self, user, fb_name, fb_id, fb_email):
        """Return data to continue interrupted registration from Facebook."""
        return {
            'intent': 'facebook_register',
            'user_id': user.id,
            'fb_id': fb_id,
            'fb_email': fb_email,
            'tmp_username': tmp_username_from_fb(fb_name, fb_id)
        }

    def init_create(self):
        """
        Create user from validated Facebook data - part 1.

        Initialize creation, setting user.is_active = False.
        We'll present in the frontend a screen where the user
        is asked to choose a username to complete her registration.
        """
        fb_id = self.validated_data.get('fb_id')
        fb_name = self.validated_data.get('fb_name')
        fb_email = self.validated_data.get('fb_email')
        pwd = crypt.crypt(
            self.validated_data.get('fb_name'),
            crypt.METHOD_MD5
        )

        # Notice we don't save the user's email.
        # As the user will be de facto deactivated, it would be
        # overriden by the email anonymizer set by deactivation.
        user_serializer = BasicUserSerializer()
        user = user_serializer.create({
            'username': tmp_username_from_fb(fb_name, fb_id),
            'password': pwd,
        }, is_active=False)

        # Return the email along with the payload.
        # It will be stored on actual activation.
        return {
            'intent': 'facebook_register',
            'user_id': user.id,
            'fb_id': fb_id,
            'fb_email': fb_email,
            'tmp_username': slugify(fb_name + '-' + fb_id)
        }

    def finish_create(self):
        """
        Create user from validated Facebook data - part 2.

        End creation, settings user.is_active = True, attaching a FacebookUser
        model to the CustomUser.
        """
        try:
            user = CustomUser.objects.get(
                username=self.validated_data.get('tmp_username', '')
            )
            tmp_username = self.validated_data.get('tmp_username')
            fb_id = self.validated_data.get('fb_id', None)

            user.username = self.validated_data.get('username', tmp_username)
            user.email = self.validated_data.get('fb_email')
            user.is_active = True
            user.attach_facebook_account(facebook_id=fb_id)
            user.save()

            return user

        except Exception as e:
            raise e


class FacebookAuthenticateUserSerializer(serializers.Serializer):
    """Serializer to authenticate existing user from FB data."""

    def validate(self, data):
        """
        Validate payload.

        Lean of FacebookUserSerializer for now.
        """
        return self.initial_data
