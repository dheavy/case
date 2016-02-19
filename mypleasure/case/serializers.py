"""CASE (MyPleasure API) serializers."""
from rest_framework import serializers
from case.models import Collection, Video, CustomUser, Tag


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

    videos = serializers.SerializerMethodField()
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
            if 'collections' in obj:
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

    class Meta:
        """Meta for BasicUserSerializer."""

        model = CustomUser
        fields = (
            'id', 'username', 'password', 'last_login', 'last_access',
            'collections', 'videos'
        )
        extra_kwargs = {
            'password': {'write_only': True},
            'confirm_password': {'write_only': True},
        }


class FullUserSerializer(BasicUserSerializer):
    """Serializer for User model as seen by staff members."""

    class Meta(BasicUserSerializer.Meta):
        """Meta for FullUserSerializer."""

        fields = BasicUserSerializer.Meta.fields + (
            'email', 'is_staff', 'is_active', 'is_superuser', 'date_joined'
        )


class CollectionSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer for Collection model."""

    class Meta:
        """Meta for Collection serializer."""

        model = Collection
        fields = (
            'id', 'name', 'owner', 'slug', 'videos', 'is_private',
            'is_default', 'created_at', 'updated_at'
        )


class VideoSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer for Video model."""

    owner = serializers.ReadOnlyField()

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
        fields = ('id', 'videos', 'slug')


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
        if existing_email:
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
