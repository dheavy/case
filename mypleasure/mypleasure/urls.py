"""Mypleasure URL Configuration.

The `urlpatterns` list routes URLs to views. For more information please see:
    https://docs.djangoproject.com/en/1.9/topics/http/urls/
Examples:
Function views
    1. Add an import:  from my_app import views
    2. Add a URL to urlpatterns:  url(r'^$', views.home, name='home')
Class-based views
    1. Add an import:  from other_app.views import Home
    2. Add a URL to urlpatterns:  url(r'^$', Home.as_view(), name='home')
Including another URLconf
    1. Import the include() function: from django.conf.urls import url, include
    2. Add a URL to urlpatterns:  url(r'^blog/', include('blog.urls'))
"""
from django.conf.urls import url
from rest_framework_jwt.views import obtain_jwt_token, refresh_jwt_token
from case.api.views import (
    UserList, UserDetail, ProfileView, RegistrationViewSet, CollectionList,
    CollectionDetail, FeedNormalList, FeedNaughtyList, VideoList, VideoDetail,
    TagList, TagDetail, UserPasswordResetViewSet
)
from case.admin import mp_admin


urlpatterns = [
    ##################
    #      Admin     #
    ##################
    url(r'^admin/?', mp_admin.urls),


    ##################
    # Authentication #
    ##################

    # Registration
    # ------------
    url(
        r'^api/v1/register/?$',
        RegistrationViewSet.as_view({'post': 'register'}),
        name='register'
    ),

    # Login
    # -----
    # Uses JWT. After obtaining JSON Web Token from login request, set a header
    # `Authorization: Bearer <jwt_token>` on each request to authenticate it.
    url(r'^api/v1/auth/login/?$', obtain_jwt_token, name='login'),

    # Refresh token
    # -------------
    # If JWT_ALLOW_REFRESH is True, issued tokens can be "refreshed" to obtain
    # a new brand token with renewed expiration time. Pass in an existing token
    # to the refresh endpoint as follows: {"token": EXISTING_TOKEN}.
    # Note that only non-expired tokens will work.
    # The JSON response looks the same as the normal obtain token endpoint
    # {"token": NEW_TOKEN}.
    #
    # Refresh with tokens can be repeated (token1 -> token2 -> token3), but
    # this chain of token stores the time that the original token (obtained
    # with username/password credentials), as orig_iat. You can only keep
    # refreshing tokens up to JWT_REFRESH_EXPIRATION_DELTA.
    #
    # A typical use case might be a web app where you'd like to keep the user
    # "logged in" the site without having to re-enter their password, or get
    # kicked out by surprise before their token expired. Imagine they had a
    # 1-hour token and are just at the last minute while they're still doing
    # something. With mobile you could perhaps store the username/password to
    # get a new token, but this is not a great idea in a browser. Each time
    # the user loads the page, you can check if there is an existing
    # non-expired token and if it's close to being expired, refresh it to
    # extend their session. In other words, if a user is actively using your
    # site, they can keep their "session" alive.
    url(r'^api/v1/auth/token/refresh?$',
        refresh_jwt_token, name='refresh-token'),

    # Password reset
    # --------------
    # Any user.
    # `get_reset` is passing token within the URL. It allows displaying a view
    # in the front end to enable user to reset her password.
    # `post_reset` effectively triggers the password reset.
    url(
        r'^api/v1/password/reset/$',
        UserPasswordResetViewSet.as_view({'get': 'get_reset'}),
        name='password-reset'
    ),

    # Exceptionally, we want to send directly to a "frontend" view,
    # not an API view.
    url(
        r'^password/reset/(?P<uidb64>[0-9A-Za-z]+)-(?P<token>.+)/$',
        UserPasswordResetViewSet.as_view({'get': 'get_reset_confirm'}),
        name='password-reset-confirm'
    ),
    url(
        r'^api/v1/password/reset/done/$',
        UserPasswordResetViewSet.as_view({'post': 'post_reset'}),
        name='password-reset-done'
    ),


    ##################
    #    Resources   #
    ##################

    # Feed
    # ----
    # Available to authenticated user. Comes in two flavor: normal/naughty.
    url(r'^api/v1/feed/?$',
        FeedNormalList.as_view(), name='feed-normal'),
    url(r'^api/v1/feed/normal/?$',
        FeedNormalList.as_view(), name='feed-normal'),
    url(r'^api/v1/feed/naughty/?$',
        FeedNaughtyList.as_view(), name='feed-naughty'),

    # Users
    # -----
    # User detail is available to any authenticated user.
    # User list is available to admin user only.
    url(r'^api/v1/users/?$', UserList.as_view(), name='customuser-list'),
    url(r'^api/v1/users/(?P<pk>[0-9]+)/?$',
        UserDetail.as_view(), name='customuser-detail'),
    url(r'^api/v1/me/?$', ProfileView.as_view(), name='me'),

    # Collections
    # -----------
    # Available to authenticated users only.
    # Private collections are only visible to their respective owners.
    url(r'^api/v1/collections/?$',
        CollectionList.as_view(), name='collection-list'),
    url(r'^api/v1/collections/(?P<pk>[0-9]+)/?$',
        CollectionDetail.as_view(), name='collection-detail'),

    # Videos
    # ------
    # Available to authenticated users only.
    # Private videos are only visible to their respective owners.
    url(r'^api/v1/videos/?$',
        VideoList.as_view(), name='video-list'),
    url(r'^api/v1/videos/(?P<pk>[0-9]+)/?$',
        VideoDetail.as_view(), name='video-detail'),

    # Tags
    # ----
    # Available to authenticated users only.
    url(r'^api/v1/tags/?$',
        TagList.as_view(), name='tag-list'),
    url(r'^api/v1/tags/(?P<pk>[0-9]+)/?$',
        TagDetail.as_view(), name='tag-detail'),
]
