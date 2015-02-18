<div class="col-sm-12 col-md-3 col-lg-3 video" data-video="{{ $video->embed_url }}" data-index="<?php echo $i ?>" style="top:30px;height:330px">
  @if ($video->method === '_dummy')
  <div class="col-sm-12 col-md-12 col-lg-12 dummy" style="display:block;width:100%;height:200px;background:#CCC"></div>
  @else
  <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
  @endif

  <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $video->title }}}</h5>
  <div class="col-sm-12 col-md-12 col-lg-12">{{{ $video->duration }}}</div>
  <ul class="col-sm-12 col-md-12 col-lg-12">
    <li><a class="play" data-index="<?php echo $i ?>" href="#">Play video</a></li>
    <li><a class="edit" href="<?php $url = "/me/videos/{$video->id}/edit"; echo URL::secure($url) ?>">Edit video</a></li>
    <li><a class="tags" href="<?php $url = "/me/videos/{$video->id}/tags/edit"; echo URL::secure($url) ?>">View/Edit tags</a></li>
    <li><a class="delete" href="<?php $url = "/me/videos/{$video->id}/delete"; echo URL::secure($url) ?>">Delete video</a></li>
  </ul>
</div>