"""CASE (MyPleasure API) admin forms."""
from django import forms
from django.contrib import admin
from .models import Video


class TagForm(forms.ModelForm):
    """Form class for Tag model."""

    videos = forms.ModelMultipleChoiceField(
        queryset=Video.objects.all(),
        widget=admin.widgets.FilteredSelectMultiple('Videos', False),
        required=False
    )

    def __init__(self, *args, **kwargs):
        """Initialize."""
        super(TagForm, self).__init__(*args, **kwargs)
        if self.instance.pk:
            # If this is not a new object, we load related Videos
            self.initial['videos'] = self.instance.videos.values_list(
                'pk', flat=True
            )

    def save(self, *args, **kwargs):
        """Save form content."""
        instance = super(TagForm, self).save(*args, **kwargs)
        if instance.pk:
            for video in instance.videos.all():
                if video not in self.cleaned_data['videos']:
                    # Remove videos which have been unselected
                    instance.videos.remove(video)
            for video in self.cleaned_data['videos']:
                if video not in instance.videos.all():
                    # Add newly selected videos
                    instance.videos.add(video)
        return instance
