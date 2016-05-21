"""Make CASE modifiable in the admin."""
from django.contrib import admin
from django.contrib.admin import AdminSite
from django.utils.translation import ugettext_lazy as _
from django.contrib.auth.admin import UserAdmin as BaseUserAdmin
from django.contrib.auth.forms import AdminPasswordChangeForm

from .models import (
    Collection, Video, Invite, Tag, CustomUser, UserReport,
    UserFollowRelationship, UserCollectionFollowRelationship
)
from .forms import (
    TagForm, CustomUserForm, CustomUserChangeForm, UserReportForm
)


class FollowingInline(admin.TabularInline):
    """Inline set up to display M2M user<->user relationships."""

    model = UserFollowRelationship
    fk_name = 'follower'


# class UserToCollectionInline(admin.TabularInline):
#     """Inline set up to display M2M user<->user relationships."""

#     model = UserCollectionFollowRelationship
#     fk_name = 'collection'


class CustomUserAdmin(BaseUserAdmin):
    """Create admin model for CustomUser."""

    # The forms to add and change user instances
    form = CustomUserChangeForm
    add_form = CustomUserForm
    change_password_form = AdminPasswordChangeForm

    # Setup for M2M relationships (users, collections).
    inlines = (FollowingInline, )

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
        'video', 'reporter', 'assignee', 'comments',
        'status', 'created_at', 'updated_at'
    )
    list_filter = ('status',)
    fieldsets = (
        (None, {'fields': ('video', 'reporter',)}),
        ('Inquiry', {'fields': ('status', 'comments',)}),
    )
    search_fields = ('status', 'reporter', 'assignee',)
    ordering = ('status', 'created_at',)


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
mp_admin.register(Collection)
mp_admin.register(Invite)
mp_admin.register(Video)
mp_admin.register(Tag, TagAdmin)
mp_admin.register(UserReport, UserReportAdmin)
