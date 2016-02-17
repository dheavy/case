"""Make CASE modifiable in the admin."""
from django.contrib import admin

from .models import Collection, Video, Invite, RememberToken, Tag, CustomUser
from .forms import TagForm


class TagAdmin(admin.ModelAdmin):
    """Create explicit ModelAdmin class for Tag to add 'videos' field."""

    form = TagForm
    fieldsets = (
        (None, {
            'fields': ('name', 'slug', 'videos')
        }),
    )


admin.site.register(RememberToken)
admin.site.register(CustomUser)
admin.site.register(Collection)
admin.site.register(Invite)
admin.site.register(Video)
admin.site.register(Tag, TagAdmin)
