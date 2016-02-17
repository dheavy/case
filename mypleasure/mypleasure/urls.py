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
from django.contrib import admin
from django.conf.urls import url
from rest_framework_jwt.views import obtain_jwt_token, refresh_jwt_token
from case.views import UserList, UserDetail, ProfileView
from case.views import CollectionList, CollectionDetail
from case.views import VideoList, VideoDetail


urlpatterns = [
    ##################
    #      Admin     #
    ##################
    url(r'^admin/', admin.site.urls),


    ##################
    # Authentication #
    ##################

    # Login
    # -----
    # Uses JWT. After obtaining JSON Web Token from login request, set a header
    # `Authorization: Bearer <jwt_token>` on each request to authenticate it.
    url(r'^api/v1/auth/login?$', obtain_jwt_token, name='login'),

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


    ##################
    #    Resources   #
    ##################

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
]
