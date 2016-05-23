"""CASE (MyPleasure API) serializers."""
import crypt
from django.core.validators import validate_email
from django.core.exceptions import ValidationError
from django.core.validators import URLValidator
from django.utils.encoding import force_text
from django.contrib.auth import get_user_model
from django.utils.http import urlsafe_base64_decode as uid_decoder
from django.contrib.auth.tokens import default_token_generator
from rest_framework import serializers
from case.models import (
    Collection, Video, CustomUser, Tag, MediaStore, MediaQueue
)
from .filters import (
    filter_videos_by_ownership_for_privacy,
    filter_videos_for_feed
)
from django.contrib.auth.forms import PasswordResetForm, SetPasswordForm


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
            raise serializers.ValidationError(
                'Invalid email address.',
                code='invalid_email'
            )

    existing_email = CustomUser.objects.filter(email=email).first()
    if existing_email and existing_email != '':
        raise serializers.ValidationError(
            'Email address already exists.',
            code='existing_email'
        )

    if not data.get('password') or not data.get('confirm_password'):
        raise serializers.ValidationError(
            'Password confirmation missing.',
            code='confirm_password_missing'
        )

    if data.get('password') != data.get('confirm_password'):
        raise serializers.ValidationError(
            'Passwords mismatch',
            code='passwords_mismatch'
        )

    return True


class BasicUserSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer class for User, as seen by regular Users."""

    # Do not include videos as they are already passed in the
    # attached collections payload. Leave it commented just in case
    # we want this feature back.
    # videos = serializers.SerializerMethodField()
    collections = serializers.SerializerMethodField()

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
                if not c.is_private or c.owner.id == request.user.id
            ]
        except:
            return []

    def create(self, validated_data):
        """Create User if validation succeeds."""
        password = validated_data.pop('password', None)
        user = self.Meta.model(**validated_data)
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

        # Do not include videos as they are already passed in the
        # attached collections payload
        fields = (
            'id', 'username', 'password', 'last_login', 'last_access',
            'collections',
            # 'videos'
        )
        extra_kwargs = {
            'password': {'write_only': True},
            'confirm_password': {'write_only': True},
        }


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


class CollectionSerializer(serializers.HyperlinkedModelSerializer):
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


class VideoSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer for Video model."""

    owner = serializers.SerializerMethodField()
    tags = serializers.PrimaryKeyRelatedField(
        queryset=Tag.objects.all(), many=True
    )

    def get_owner(self, obj):
        """Returned serialized owner of the Video."""
        return BasicUserSerializer(obj.collection.owner).data

    class Meta:
        """Meta for Video serializer."""

        model = Video
        fields = (
            'id', 'title', 'hash', 'slug', 'poster', 'original_url',
            'embed_url', 'duration', 'is_naughty', 'created_at', 'tags',
            'updated_at', 'collection', 'owner', 'is_private',
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
        return filter_videos_for_feed(
            self.context['request'],
            Video.objects.filter(is_naughty=False),
            FeedPublicVideoSerializer, FeedPrivateVideoSerializer
        )


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
            'embed_url', 'duration', 'is_naughty', 'tags'
        )


class FeedPublicVideoSerializer(FeedPrivateVideoSerializer):
    """Serializer for *single* video, in Feed with owner info."""

    class Meta(FeedPrivateVideoSerializer.Meta):
        """Meta for FeedPublicVideoSerializer."""

        fields = FeedPrivateVideoSerializer.Meta.fields + ('owner',)


class TagSerializer(serializers.HyperlinkedModelSerializer):
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

    It does not create the user (that's left to the CustomUserSerializer).
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
        """
        if type(self.context['request'].user) is get_user_model():
            raise serializers.ValidationError(
                'Action forbidden for authenticated user forbidden',
                code='auth_forbidden'
            )

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
            raise serializers.ValidationError({'uid': ['Invalid value']})

        # Construct SetPasswordForm instance
        self.set_password_form = self.set_password_form_class(
            user=self.user, data=data
        )
        if not self.set_password_form.is_valid():
            raise serializers.ValidationError(self.set_password_form.errors)
        if not default_token_generator.check_token(self.user, data['token']):
            raise serializers.ValidationError({'token': ['Invalid value']})

        return data

    def save(self):
        """Save new password."""
        self.set_password_form.save()


class CuratedMediaAcquisitionSerializer(serializers.Serializer):
    """Serializer used when performing a media acquisition."""

    def validate(self, attrs):
        """Attempt validation of attributes."""
        attrs = self.initial_data

        # If collection_id is provided, check if it exists,
        # belongs to user.
        if 'collection_id' in attrs:
            try:
                c = Collection.objects.get(pk=attrs['collection_id'])
                assert c.owner == self.context['request'].user
            except:
                raise serializers.ValidationError({
                    'code': 'collection_id_invalid'
                })
        else:
            try:
                # If not ID provided, a new collection name should have been.
                name = attrs['new_collection_name'][0]
                assert bool(name) is not None and name != ''
            except:
                raise serializers.ValidationError({
                    'code': 'collection_id_or_name_missing'
                })

        # Verify URL presence and validity.
        if 'url' not in attrs:
            raise serializers.ValidationError({'code': 'url_missing'})

        try:
            URLValidator()(attrs['url'])
        except ValidationError:
            raise serializers.ValidationError({'code': 'url_invalid'})

        # Prevent duplicates.
        u = self.context['request'].user
        if u.has_video(url=attrs['url'], include_queue=True):
            raise serializers.ValidationError({'code': 'duplicate'})

        return attrs

    def save(self):
        """Save Video in store."""
        hash = crypt.crypt(
            self.validated_data['url'],
            crypt.METHOD_MD5
        )

        # If found in store, get this previously cached version.
        cached_video = MediaStore.objects.filter(hash=hash)
        if len(cached_video) > 0:
            self.get_from_store(cached_video[0])
            return {'code': 'available'}
        else:
            if 'collection_id' in self.validated_data:
                cid = self.validated_data['collection_id']
            else:
                new_collection = Collection.objects.create(
                    name=self.validated_data['new_collection_name'],
                    owner=self.context['request'].user
                )
                cid = new_collection.id

            self.add_to_queue(
                hash=hash,
                url=self.validated_data['url'],
                requester=self.context['request'].user.id,
                collection_id=cid
            )
            return {'code': 'added'}

    def add_to_queue(self, hash, url, requester, collection_id):
        """Add job to media queue."""
        MediaQueue.objects.create(
            hash=hash, url=url, requester=requester,
            collection_id=collection_id, status='pending'
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
            raise serializers.ValidationError({'code': 'user_not_found'})

        if self.initial_data.get('intent', None) == 'follow':
            self.initial_data.get('current_user', None).follow_user(other_user)
        elif self.initial_data.get('intent', None) == 'unfollow':
            # Will return True if unfollowed successful, False otherwise.
            if self.initial_data.get(
                'current_user', None
            ).unfollow_user(other_user) is False:
                raise serializers.ValidationError({'code': 'unfollow_failed'})
        else:
            raise serializers.ValidationError({'code': 'intent_misunderstood'})

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
