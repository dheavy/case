"""Make CASE modifiable in the admin."""
from django.contrib import admin
from django.contrib.auth.admin import UserAdmin as BaseUserAdmin
from django.contrib.auth.forms import AdminPasswordChangeForm

from .models import Collection, Video, Invite, RememberToken, Tag, CustomUser
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
    list_display = ('username', 'email', 'is_superuser', 'is_staff')
    list_filter = ('is_superuser', 'is_staff',)
    fieldsets = (
        (None, {'fields': ('username', 'password',)}),
        ('Permissions', {'fields': ('is_superuser', 'is_staff',)}),
    )
    # add_fieldsets is not a standard ModelAdmin attribute. BaseUserAdmin
    # overrides get_fieldsets to use this attribute when creating a user.
    add_fieldsets = (
        (None, {
            'classes': ('wide',),
            'fields': ('email', 'date_of_birth', 'password1', 'password2')}),
    )
    search_fields = ('email',)
    ordering = ('email',)
    filter_horizontal = ()


class TagAdmin(admin.ModelAdmin):
    """Create explicit ModelAdmin class for Tag to add 'videos' field."""

    form = TagForm
    fieldsets = (
        (None, {
            'fields': ('name', 'slug', 'videos')
        }),
    )


admin.site.register(RememberToken)
admin.site.register(CustomUser, CustomUserAdmin)
admin.site.register(Collection)
admin.site.register(Invite)
admin.site.register(Video)
admin.site.register(Tag, TagAdmin)
