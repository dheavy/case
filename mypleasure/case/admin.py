"""Make CASE modifiable in the admin."""
from django.contrib import admin
from django.contrib.admin import AdminSite
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.admin import UserAdmin as BaseUserAdmin
from django.contrib.auth.forms import AdminPasswordChangeForm

from .models import (
    Collection, Video, Invite, Tag, CustomUser, UserReport,
    UserFollowRelationship, UserCollectionFollowRelationship,
    UserBlockRelationship, UserCollectionBlockRelationship,
    MediaStore, MediaQueue
)
from .forms import (
    TagForm, CustomUserForm, CustomUserChangeForm, UserReportForm,
    VideoForm, MediaStoreForm
)


class FollowingInline(admin.TabularInline):
    """Inline set up to display M2M user<->user (follow) relationships."""

    model = UserFollowRelationship
    fk_name = 'follower'


class BlockingInline(admin.TabularInline):
    """Inline set up to display M2M user<->collection (block) relationships."""

    model = UserBlockRelationship
    fk_name = 'blocker'


class FollowingCollectionInline(admin.TabularInline):
    """Inline set up to display M2M user<->collect. (follow) relationships."""

    model = UserCollectionFollowRelationship
    fk_name = 'user'


class BlockingCollectionInline(admin.TabularInline):
    """Inline set up to display M2M user<->user (block) relationships."""

    model = UserCollectionBlockRelationship
    fk_name = 'user'


class CustomUserAdmin(BaseUserAdmin):
    """Create admin model for CustomUser."""

    # The forms to add and change user instances
    form = CustomUserChangeForm
    add_form = CustomUserForm
    change_password_form = AdminPasswordChangeForm

    # Setup for M2M relationships (users, collections).
    inlines = (
        FollowingInline, BlockingInline, FollowingCollectionInline,
        BlockingCollectionInline
    )

    # The fields to be used in displaying the User model.
    # These override the definitions on the base BaseUserAdmin
    # that reference specific fields on auth.User.
    list_display = (
        'id', 'username', 'email', 'is_superuser', 'is_staff', 'is_active',
        'date_joined', 'last_access', 'updated_at'
    )
    list_filter = ('is_superuser', 'is_staff', 'is_active')
    fieldsets = (
        (None, {'fields': ('username', 'email', 'password',)}),
        ('Account status', {'fields': ('is_active',)}),
        ('Permissions', {'fields': ('is_superuser', 'is_staff',)}),
    )
    # add_fieldsets is not a standard ModelAdmin attribute. BaseUserAdmin
    # overrides get_fieldsets to use this attribute when creating a user.
    add_fieldsets = (
        (None, {
            'classes': ('wide',),
            'fields': ('username', 'email', 'password1', 'password2')}),
    )
    search_fields = ('username',)
    ordering = ('username',)
    filter_horizontal = ()


class TagAdmin(admin.ModelAdmin):
    """Create explicit ModelAdmin class for Tag to add 'videos' field."""

    form = TagForm
    fieldsets = (
        (None, {
            'fields': ('name', 'slug', 'videos')
        }),
    )


class UserReportAdmin(admin.ModelAdmin):
    """Custom admin for UserReport."""

    form = UserReportForm
    list_display = (
        'id', 'video', 'reporter', 'assignee', 'comments',
        'status', 'created_at', 'updated_at'
    )
    list_filter = ('status',)
    fieldsets = (
        (None, {'fields': ('video', 'reporter',)}),
        ('Inquiry', {'fields': ('status', 'comments',)}),
    )
    search_fields = ('status', 'reporter', 'assignee',)
    ordering = ('status', 'created_at',)


class VideoAdmin(admin.ModelAdmin):
    """Custom admin for Video."""

    readonly_fields = ('duration', 'hash',)
    form = VideoForm
    list_display = (
        'id', 'scale', 'title', 'is_naughty', 'duration', 'poster',
        'original_url', 'embed_url', 'slug', 'hash',
        'created_at', 'updated_at',
    )
    list_filter = ('scale', 'is_naughty', 'duration',)
    search_fields = ('title', 'poster', 'original_url', 'hash',)
    fieldsets = (
        ('Editorialization / Classification', {
            'fields': ('title', 'scale', 'duration', 'is_naughty',)
        }),
        ('Media', {'fields': ('poster', 'original_url', 'embed_url',)}),
        ('Meta', {'fields': ('hash', 'slug',)}),
    )


class MediaQueueAdmin(admin.ModelAdmin):
    """Custom admin for MediaStore."""

    readonly_fields = ('hash', 'status',)
    list_display = (
        'id', 'status', 'url', 'requester', 'collection_id', 'created_at',
    )
    list_filter = ('status', 'requester', 'created_at',)
    search_fields = ('status', 'url',)


class MediaStoreAdmin(admin.ModelAdmin):
    """Custom admin for MediaStore."""

    readonly_fields = ('duration', 'hash',)
    form = MediaStoreForm
    list_display = (
        'id', 'title', 'naughty', 'duration', 'poster', 'original_url',
        'embed_url', 'hash', 'created_at',
    )
    list_filter = ('naughty', 'duration',)
    search_fields = ('title', 'poster', 'original_url', 'hash',)


class MyPleasureAdmin(AdminSite):
    """Customize elements from the admin panel itself."""

    # Text to put at the end of each page's <title>.
    site_title = _('MyPleasure Admin Panel')

    # Text to put in each page's <h1>.
    site_header = _('MyPleasure')

    # Text to put at the top of the admin index page.
    index_title = _('Site administration')


mp_admin = MyPleasureAdmin()

mp_admin.register(CustomUser, CustomUserAdmin)
mp_admin.register(MediaStore, MediaStoreAdmin)
mp_admin.register(MediaQueue, MediaQueueAdmin)
mp_admin.register(Collection)
mp_admin.register(Invite)
mp_admin.register(Video, VideoAdmin)
mp_admin.register(Tag, TagAdmin)
mp_admin.register(UserReport, UserReportAdmin)
