"""Make CASE modifiable in the admin."""
from django.contrib import admin

from .models import Collection, Video, Invite, RememberToken, Tag, MPUser


admin.site.register(RememberToken)
admin.site.register(Collection)
admin.site.register(MPUser)
admin.site.register(Invite)
admin.site.register(Video)
admin.site.register(Tag)
