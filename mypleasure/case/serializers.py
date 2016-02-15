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
            'is_superuser', 'date_joined', 'collections'
        )
        write_only_fields = ('password', 'confirm_password')
        read_only_fields = (
            'is_staff', 'is_superuser', 'is_active', 'date_joined', ''
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

    owner = serializers.ReadOnlyField(source='owner.username')

    class Meta:
        """Meta for Collection serializer."""

        model = Collection
        fields = (
            'id', 'name', 'owner', 'slug', 'videos',
            'is_private', 'created_at', 'updated_at'
        )


class VideoSerializer(serializers.HyperlinkedModelSerializer):
    """Serializer for Video model."""

    class Meta:
        """Meta for Video serializer."""

        model = Video
        fields = ('id', 'title')
