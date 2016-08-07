"""CASE (MyPleasure API) admin forms."""
from django import forms
from django.contrib import admin
from .models import Video, CustomUser, UserReport
from django.contrib.auth.forms import ReadOnlyPasswordHashField
from django.contrib.auth.forms import PasswordResetForm


class CustomUserForm(forms.ModelForm):
    """Form class for CustomUser model."""

    password1 = forms.CharField(
        label='Password', widget=forms.PasswordInput
    )
    password2 = forms.CharField(
        label='Password confirmation', widget=forms.PasswordInput
    )

    class Meta:
        """Meta for CustomUserForm."""

        model = CustomUser
        fields = (
            'username', 'password', 'is_superuser', 'is_staff', 'is_active'
        )

    def clean_password2(self):
        """Check that the two password entries match."""
        password1 = self.cleaned_data.get('password1')
        password2 = self.cleaned_data.get('password2')
        if password1 and password2 and password1 != password2:
            raise forms.ValidationError('Passwords don\'t match.')
        return password2

    def save(self, commit=True):
        """Save the provided password in hashed format."""
        user = super(CustomUserForm, self).save(commit=False)
        user.set_password(self.cleaned_data['password1'])
        if commit:
            user.save()
        return user


class UserReportForm(forms.ModelForm):
    """Form for managing user reports."""

    status = forms.ChoiceField(
        widget=forms.Select,
        choices=UserReport.objects.statuses_for_form,
        help_text=(
            "<b>new</b> is the state given by default for any new report" +
            "<br /><b>reviewing</b> is the state set by a staff member when" +
            "reviewing the report<br /><b>accepted</b> is set by reviewer" +
            "when inquiry validates report, and video is effectively being" +
            "removed<br /> <b>dismissed</b> is set by reviewer after inquiry" +
            "to ignore report."
        )
    )


class CustomUserChangeForm(forms.ModelForm):
    """
    A form for updating users.

    Includes all the fields on the user, but replaces the password field
    with admin's password hash display field.
    """

    password = ReadOnlyPasswordHashField(
        label=('Password'),
        help_text=(
            "Raw passwords are not stored, so there is no way to see " +
            "this user's password, but you can change the password " +
            "using <a href=\'../password/\'>this form</a>."
        )
    )

    is_active = forms.ChoiceField(
        widget=forms.RadioSelect,
        choices=((True, 'Active'), (False, 'Deactivated'),),
        label=('Active status'),
        help_text=(
            "Setting an account to inactive makes the user <strong>unable " +
            "to use her account.</strong> It's what happens when user " +
            "chooses to deactivate her account. So be careful!."
        )
    )

    class Meta:
        """Meta for CustomUserChangeForm."""

        model = CustomUser
        fields = (
            'username', 'email', 'password', 'is_superuser',
            'is_active', 'is_staff'
        )

    def clean_password(self):
        """
        Regardless of what the user provides, return the initial value.

        This is done here, rather than on the field, because the
        field does not have access to the initial value.
        """
        return self.initial["password"]


class VideoForm(forms.ModelForm):
    """Form class for Video model."""

    scale = forms.ChoiceField(
        widget=forms.Select,
        choices=Video.objects.scales_for_form,
        help_text=(
            "Use <b>large</b> for videos you want to highlight " +
            "on feeds (i.e. when editorializing content)."
        )
    )


class MediaStoreForm(forms.ModelForm):
    """Form class for MediaStore model."""

    naughty = forms.ChoiceField(
        widget=forms.Select,
        choices=((True, 'Naughty mode'), (False, 'Normal mode'),),
        label=('View mode'),
    )


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


class UserForgotPasswordForm(PasswordResetForm):
    """Password reset form."""

    email = forms.EmailField(required=True, max_length=254)

    class Meta:
        """Meta for UserForgotPassword."""

        model = CustomUser
        fields = ('email',)
