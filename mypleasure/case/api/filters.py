"""CASE (MyPleasure API) views."""
from django.shortcuts import get_object_or_404
from rest_framework.exceptions import PermissionDenied


def filter_videos_for_feed(request, videos, pb_serializer, pv_serializer):
    """
    Filter videos for the Feed.

    Private videos are purged frofom information about the owner.
    Uses FeedVideoSerializer.
    """
    try:
        def serializer_class(v):
            if v.is_private is False:
                return pb_serializer
            return pv_serializer

        return [
            serializer_class(v)(
                v, context={'request': request}
            ).data for v in videos
        ]
    except:
        return []


def filter_videos_by_ownership_for_privacy(request, obj, serializer):
    """
    Filter videos before passing them down to serializers.

    Private videos are excluded if current user don't own them.
    """
    try:
        # Either a list, or a ManyRelatedManager.
        videos = type(obj.videos) == list and obj.videos or obj.videos.all()

        return [
            serializer(
                v, context={'request': request}
            ).data for v in videos
            if not v.is_private or v.owner['id'] == request.user.id
        ]
    except:
        return []


def filter_private_obj_list_by_ownership(obj, user):
    """Filter a QuerySet based on whether user owns the object if private."""
    qsets = obj.objects.all()
    return [o for o in qsets if (not o.is_private or o.owner == user)]


def filter_private_obj_detail_by_ownership(
    queryset,
    check_object_permissions,
    request
):
    """Filter detail view object based on whether user owns it if private."""
    obj = get_object_or_404(queryset)
    check_object_permissions(request, obj)
    id = None
    try:
        id = obj.owner['id']
    except:
        id = obj.owner.id
    if obj.is_private and id != request.user.id:
        raise PermissionDenied(detail='Item is private.')
    return obj


def filter_feed_by_naughtyness(video_set, is_naughty):
    """Filter feed based on naughtyness."""
    r = [
        v for v in video_set if v.is_naughty == is_naughty
    ]
    return r


def filter_feed_by_user(user, is_naughty=False):
    """Return feed based on what user follows."""
    feed = []
    collections_followed = user.collections_followed

    # Add own videos.
    feed += [
        v for v in user.videos if v.is_naughty is is_naughty
    ]

    # Add videos from followed collections.
    # Remove blocked content if inadvertently followed.
    feed += [
        v for c in collections_followed
        for v in c.videos.all()
        if v.is_naughty is is_naughty and
        v not in feed
    ]

    return feed


def filter_feed_by_video_unicity(original_feed):
    """
    Return a feed where list is reduced to avoid duplicate videos.

    Only the first instance of the video is registered,
    the rest is ignored.
    """
    filtered = []
    seen = set()

    for v in original_feed:
        if v.hash not in seen:
            filtered.append(v)
            seen.add(v.hash)

    return filtered
