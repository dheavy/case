<div class="col-sm-12 col-md-3 col-lg-3 video" data-video="{{ $embed_url }}" data-index="<?php echo $index ?>" style="top:30px;height:330px">
  @if ($method === '_dummy')
  <div class="col-sm-12 col-md-12 col-lg-12 dummy" style="display:block;width:100%;height:200px;background:#CCC"></div>
  @else
  <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $poster }}" width="100%">
  @endif

  <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $title }}}</h5>
  <div class="col-sm-12 col-md-12 col-lg-12">{{{ $duration }}}</div>
  <ul class="col-sm-12 col-md-12 col-lg-12">
    <li><a class="play" data-index="<?php echo $index ?>" href="#">Play video</a></li>

    @if (isset($username))
      <div class="col-sm-12 col-md-12 col-lg-12">curated by {{{ $username }}}</div>
    @else
      <li><a class="edit" href="<?php $url = "/me/videos/{$id}/edit"; echo URL::secure($url) ?>">Edit video</a></li>
      <li><a class="tags" href="<?php $url = "/me/videos/{$id}/tags/edit"; echo URL::secure($url) ?>">View/Edit tags</a></li>
      <li><a class="delete" href="<?php $url = "/me/videos/{$id}/delete"; echo URL::secure($url) ?>">Delete video</a></li>
    @endif
  </ul>
</div>