<h4 class="col-sm-12 col-md-12 col-lg-12">{{ Lang::get('collections.single.title', array('name' => $name))}}

<?php
  if (isset($count) && isset($isPublic)):
    $status = $isPublic ? Lang::get('collections.single.public') : Lang::get('collections.single.private');
?>
<small>{{ Lang::choice('collections.single.info', $count, array('count' => $count, 'status' => $status)) }}</small>
<?php endif; ?>

[
<a href="<?php echo URL::secure("/me/collections/{$id}") ?>">{{ Lang::get('collections.single.view') }}</a> |
<a href="<?php echo URL::secure("/me/collections/{$id}/edit/") ?>">{{ Lang::get('collections.single.edit') }}</a>

@if (!$isDefault)
 | <a href="<?php echo URL::secure("/me/collections/{$id}/delete/") ?>">{{ Lang::get('collections.single.delete') }}</a>
@endif
]
</h4>