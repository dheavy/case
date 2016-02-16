"""CASE (MyPleasure API) serializers."""
from django.contrib.auth.models import User
from rest_framework import serializers
from case.models import Collection, Video


class UserSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer for User model."""

    class Meta:
        """Meta for User serializer."""

        model = User
        fields = (
            'id', 'username', 'email', 'password', 'is_staff',
            'is_superuser', 'date_joined', 'last_login', 'collections'
        )
        extra_kwargs = {
            'password': {'write_only': True},
            'confirm_password': {'write_only': True},
        }
        read_only_fields = (
            'is_staff', 'is_superuser', 'is_active',
            'date_joined', 'last_login'
        )

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
            'updated_at', 'collection', 'owner'
        )
