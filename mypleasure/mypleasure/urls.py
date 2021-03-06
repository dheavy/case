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
from django.conf import settings
from rest_framework_jwt.views import obtain_jwt_token, refresh_jwt_token
from case.api.views import (
    UserList, UserDetail, ProfileView, RegistrationViewSet, CollectionList,
    CollectionDetail, FeedView, VideoList, SearchViewSet,
    VideoDetail, TagList, TagDetail, PasswordResetView,
    PasswordResetConfirmView, CuratedMediaViewSet, HeartbeatViewSet,
    FollowUserViewSet, FollowCollectionViewSet, BlockUserViewSet,
    BlockCollectionViewSet, FacebookAuthViewSet, EditAccountViewSet,
    UserCollectionList, RelationshipsViewSet, VideoHashViewSet
)
from case.admin import mp_admin


urlpatterns = [
    ##################
    #      Admin     #
    ##################
    url(settings.ADMIN_URL, mp_admin.urls),

    ##################
    #    Test API    #
    ##################

    # Testing API responsiveness.
    # Simply returns HTTP 200 on all major verbs.
    url(
        r'^api/v1/heartbeat/?$',
        HeartbeatViewSet.as_view({
            'get': 'test', 'head': 'test',
            'post': 'test', 'put': 'test',
            'delete': 'test'
        })
    ),

    # Same, for authenticated user only.
    url(
        r'^api/v1/heartbeat/auth?$',
        HeartbeatViewSet.as_view({
            'get': 'test_authenticated', 'head': 'test_authenticated',
            'post': 'test_authenticated', 'put': 'test_authenticated',
            'delete': 'test_authenticated'
        })
    ),

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
    url(
        r'^api/v1/register/facebook/?$',
        RegistrationViewSet.as_view({'post': 'facebook_register'}),
        name='register-from-facebook'
    ),
    url(
        r'^api/v1/register/check/username/(?P<username>.+)/?$',
        RegistrationViewSet.as_view({'get': 'check_username'}),
        name='register-check-username'
    ),

    # Create and/or authenticate from FB user.
    url(
        r'^api/v1/auth/facebook/?$',
        FacebookAuthViewSet.as_view({'post': 'auth'}),
        name='facebook'
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
    # `password-reset-link` trigger the email verification process
    # and then calls on `password-reset-process` to send an email.
    # Then the link in the email sends back to frontend and lets
    # frontend form confirm the action after verifications.
    # `password-reset-confirm` effectively triggers the password reset.
    url(
        r'^api/v1/password/reset/?$',
        PasswordResetView.as_view(),
        name='password-reset-link',
    ),
    url(
        r'^password/reset/confirm/' +
        r'(?P<uidb64>[0-9A-Za-z]+)-(?P<token>.+)/?$',
        PasswordResetView.as_view(),
        name='password-reset-process',
    ),

    # User lands on view in frontend - a form for password
    # reset - which is the same URL as this one, attainable via GET.
    #
    # i.e.:
    # url(
    #     r'^password/reset/confirm/(?P<uidb64>[0-9A-Za-z]+)-(?P<token>.+)/?$',
    #     PasswordResetConfirmView.as_view(),
    #     name='password-reset'
    # ),

    # This is the API endpoint the password reset form should POST into.
    url(
        r'^api/v1/password/reset/confirm/' +
        r'(?P<uidb64>[0-9A-Za-z]+)-(?P<token>.+)/?$',
        PasswordResetConfirmView.as_view(),
        name='password-reset-confirm'
    ),

    ##################
    #    Resources   #
    ##################

    # Feed
    # ----
    # Available to authenticated user. Comes in two flavor: normal/naughty.
    url(r'^api/v1/feed/?(?P<pk>[0-9]+)?$',
        FeedView.as_view(), name='feed-normal'),
    url(r'^api/v1/feed/?(?P<pk>[0-9]+)?/normal/?$',
        FeedView.as_view(), name='feed-normal'),
    url(r'^api/v1/feed/?(?P<pk>[0-9]+)?/naughty/?$',
        FeedView.as_view(), name='feed-naughty'),

    # Users
    # -----
    # User detail is available to any authenticated user.
    # User list is available to admin user only.
    url(r'^api/v1/users/?$', UserList.as_view(), name='user-list'),
    url(r'^api/v1/users/(?P<pk>[0-9]+)/?$',
        UserDetail.as_view(), name='user-detail'),
    url(r'^api/v1/me/?$', ProfileView.as_view(), name='me'),

    # Assets, by users
    # ----------------
    url(r'^api/v1/users/(?P<pk>[0-9]+)/collections/?$',
        UserCollectionList.as_view(), name='user-collection-list'),

    url(
        r'^api/v1/users/(?P<pk>[0-9]+)/followed/?',
        RelationshipsViewSet.as_view({'get': 'get_users_followed'}),
        name='followed-users'
    ),

    url(
        r'^api/v1/users/(?P<pk>[0-9]+)/followers/?',
        RelationshipsViewSet.as_view({'get': 'get_users_followers'}),
        name='followers-users'
    ),

    url(
        r'^api/v1/users/blocked/?',
        RelationshipsViewSet.as_view({'get': 'get_users_blocked'}),
        name='blocked-users'
    ),

    url(
        r'^api/v1/collections/blocked/?',
        RelationshipsViewSet.as_view({'get': 'get_collections_blocked'}),
        name='blocked-collections'
    ),

    # Check if user has a video based on the video hash.
    url(
        r'^api/v1/users/(?P<pk>[0-9]+)/hash/(?P<hash>[A-Za-z0-9]+)?',
        VideoHashViewSet.as_view({'get': 'has_video'})
    ),

    # Users' settings edit (email/password)
    # -------------------------------------
    url(r'^api/v1/edit/password/?$', EditAccountViewSet.as_view({
        'post': 'edit_password'
    }), name='edit-password'),
    url(r'^api/v1/edit/email/?$', EditAccountViewSet.as_view({
        'post': 'edit_email'
    }), name='edit-email'),

    # Collections
    # -----------
    # Available to authenticated users only.
    # Private collections are only visible to their respective owners.
    url(r'^api/v1/collections/?$',
        CollectionList.as_view(), name='collection-list'),
    url(r'^api/v1/collections/(?P<pk>[0-9]+)/?$',
        CollectionDetail.as_view(), name='collection-detail'),

    # Relationships
    # -------------
    # Available to authenticated users only.
    # Follow/unfollow user, block/unblock user,
    # follow/unfollow collection, block/unblock collection.
    url(r'^api/v1/users/(?P<pk>[0-9]+)/follow/?$',
        FollowUserViewSet.as_view({'post': 'follow'}),
        name='follow-user'),
    url(r'^api/v1/users/(?P<pk>[0-9]+)/unfollow/?$',
        FollowUserViewSet.as_view({'post': 'unfollow'}),
        name='unfollow-user'),
    url(r'^api/v1/users/(?P<pk>[0-9]+)/block/?$',
        BlockUserViewSet.as_view({'post': 'block'}),
        name='block-user'),
    url(r'^api/v1/users/(?P<pk>[0-9]+)/unblock/?$',
        BlockUserViewSet.as_view({'post': 'unblock'}),
        name='unblock-user'),
    url(r'^api/v1/collections/(?P<pk>[0-9]+)/follow/?$',
        FollowCollectionViewSet.as_view({'post': 'follow'}),
        name='follow-collection'),
    url(r'^api/v1/collections/(?P<pk>[0-9]+)/unfollow/?$',
        FollowCollectionViewSet.as_view({'post': 'unfollow'}),
        name='unfollow-collection'),
    url(r'^api/v1/collections/(?P<pk>[0-9]+)/block/?$',
        BlockCollectionViewSet.as_view({'post': 'block'}),
        name='block-user'),
    url(r'^api/v1/collections/(?P<pk>[0-9]+)/unblock/?$',
        BlockCollectionViewSet.as_view({'post': 'unblock'}),
        name='unblock-user'),

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

    # Curated media
    # -------------
    # Available to authenticated users only.
    # It includes media queue and media store items,
    # and all that has to do with acquiring new media.
    url(r'^api/v1/curate/acquire/?$',
        CuratedMediaViewSet.as_view({'post': 'acquire'}),
        name='media-acquire'),
    url(r'^api/v1/curate/fetch/(?P<userid>[0-9]+)/?$',
        CuratedMediaViewSet.as_view({'get': 'fetch'}),
        name='media-fetch'),

    # Search
    # ------
    url(r'^api/v1/search/?$',
        SearchViewSet.as_view({'post': 'search'}),
        name='search'),
]
