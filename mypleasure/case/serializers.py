"""CASE (MyPleasure API) serializers."""
from rest_framework import serializers
from case.models import Collection, Video, CustomUser, Tag


def get_user_serializer(user, pk=None):
    """Return user serializer matching user's level of power."""
    if user.is_staff:    # Full if user is staff member
        return FullUserSerializer
    elif user.id is pk:  # Basic, with email, if self
        return ProfileUserSerializer
    return BasicUserSerializer


def get_videos_filtered_by_ownership_for_privacy(request, obj):
    """
    Filter videos before passing them down to serializers.

    Private videos are excluded if current user don't own them.
    """
    try:
        # Either a list, or a ManyRelatedManager.
        videos = type(obj.videos) == list and obj.videos or obj.videos.all()

        return [
            VideoSerializer(
                v, context={'request': request}
            ).data for v in videos
            if not v.is_private or v.owner['id'] == request.user.id
        ]
    except:
        return []


class BasicUserSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer class for User, as seen by regular Users."""

    # Do not include videos as they are already passed in the
    # attached collections payload. Leave it commented just in case
    # we want this feature back.
    # videos = serializers.SerializerMethodField()
    collections = serializers.SerializerMethodField()

    def get_videos(self, obj):
        """Get filtered list of serialized Videos."""
        return get_videos_filtered_by_ownership_for_privacy(
            self.context['request'], obj
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
        return get_videos_filtered_by_ownership_for_privacy(
            self.context['request'], obj
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

    def get_owner(self, obj):
        """Returned serialized owner of the Video."""
        return BasicUserSerializer(obj.collection.owner).data

    class Meta:
        """Meta for Video serializer."""

        model = Video
        fields = (
            'id', 'title', 'hash', 'slug', 'poster', 'original_url',
            'embed_url', 'duration', 'is_naughty', 'created_at',
            'updated_at', 'collection', 'owner', 'is_private'
        )


class TagSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer for Tag model."""

    videos = serializers.SerializerMethodField()

    def get_videos(self, obj):
        """Get filtered list of serialized Videos."""
        return get_videos_filtered_by_ownership_for_privacy(
            self.context['request'], obj
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

    username = serializers.CharField()
    email = serializers.EmailField(required=False)
    password = serializers.CharField()
    confirm_password = serializers.CharField()

    def validate(self, data):
        """
        Validate incoming data.

        Email uniqueness if provided, password and confirmation.
        """
        email = data.get('email', None)
        existing_email = CustomUser.objects.filter(email=email).first()
        if existing_email and existing_email != '':
            raise serializers.ValidationError(
                "Someone with that email address has already registered."
            )

        if not data.get('password') or not data.get('confirm_password'):
            raise serializers.ValidationError(
                "Please enter a password and confirm it."
            )

        if data.get('password') != data.get('confirm_password'):
            raise serializers.ValidationError("Those passwords don't match.")
        return data

    class Meta:
        """Meta for Tag serializer."""

        fields = ('username', 'email', 'password', 'confirm_password')
