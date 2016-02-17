"""Make CASE modifiable in the admin."""
from django.contrib import admin

from .models import Collection, Video, Invite, RememberToken, Tag, CustomUser


admin.site.register(RememberToken)
admin.site.register(CustomUser)
admin.site.register(Collection)
admin.site.register(Invite)
admin.site.register(Video)
admin.site.register(Tag)
