"""CASE (MyPleasure API) serializers."""
from rest_framework import serializers
from case.models import Collection, Video, CustomUser


class BasicUserSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer class for User, as seen by regular Users."""

    videos = serializers.SerializerMethodField()

    def get_videos(self, obj):
        """Get filtered list of serialized Videos."""
        request = self.context['request']
        return [
            VideoSerializer(
                v, context={'request': request}
            ).data for v in obj.videos
            if not v.is_private or v.owner['id'] == request.user.id
        ]

    class Meta:
        """Meta for BasicUserSerializer."""

        model = CustomUser
        fields = (
            'id', 'username', 'last_login', 'last_access',
            'collections', 'videos'
        )
        extra_kwargs = {
            'password': {'write_only': True},
            'confirm_password': {'write_only': True},
        }

    def validate(self, data):
        """Compare password and password confirmation."""
        if 'password' not in data.keys() or (
            data['password'] != data.pop('confirm_password')
        ):
            raise serializers.ValidationError('Password do not match')
        return data

    def create(self, validated_data):
        """Create User if validation succeeds."""
        password = validated_data.pop('password', None)
        user = self.Meta.model(**validated_data)
        user.set_password(password)
        user.save()
        return user


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
