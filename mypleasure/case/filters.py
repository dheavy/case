"""CASE (MyPleasure API) views."""
from django.shortcuts import get_object_or_404
from rest_framework.exceptions import PermissionDenied


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
