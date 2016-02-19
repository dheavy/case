"""Make CASE modifiable in the admin."""
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin as BaseUserAdmin
from django.contrib.auth.forms import AdminPasswordChangeForm

from .models import Collection, Video, Invite, Tag, CustomUser
from .forms import TagForm, CustomUserForm, CustomUserChangeForm


class CustomUserAdmin(BaseUserAdmin):
    """Create admin model for CustomUser."""

    # The forms to add and change user instances
    form = CustomUserChangeForm
    add_form = CustomUserForm
    change_password_form = AdminPasswordChangeForm

    # The fields to be used in displaying the User model.
    # These override the definitions on the base BaseUserAdmin
    # that reference specific fields on auth.User.
    list_display = (
        'id', 'username', 'email', 'is_superuser', 'is_staff', 'is_active',
        'date_joined', 'last_access'
    )
    list_filter = ('is_superuser', 'is_staff', 'is_active')
    fieldsets = (
        (None, {'fields': ('username', 'password',)}),
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


admin.site.register(CustomUser, CustomUserAdmin)
admin.site.register(Collection)
admin.site.register(Invite)
admin.site.register(Video)
admin.site.register(Tag, TagAdmin)
