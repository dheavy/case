<h4 class="col-sm-12 col-md-12 col-lg-12">Collection "{{ $name }}"

<?php if (isset($count) && isset($isPublic)): ?>
<span>
  @if ($isPublic)
  (public with
  @else
  (private with
  @endif
  @if($count <= 1)
  {{$count}} video)
  @else
  {{$count}} videos)
  @endif
</span>
<?php endif; ?>

[
<a href="<?php echo URL::secure("/me/collections/{$id}") ?>">view</a> |
<a href="<?php echo URL::secure("/me/collections/{$id}/edit/") ?>">edit</a>

@if (!$isDefault)
 | <a href="<?php echo URL::secure("/me/collections/{$id}/delete/") ?>">delete</a>
@endif
]
</h4>