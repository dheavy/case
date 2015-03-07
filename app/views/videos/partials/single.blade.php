<div class="col-sm-12 col-md-3 col-lg-3 video" data-video="{{ $embed_url }}" data-index="<?php echo $index ?>" style="margin-top:30px;margin-right:20px;height:auto;background:#FFF;border-radius:5px;-webkit-box-shadow: 0 1px 2px rgba(0,0,0,.075);box-shadow: 0 1px 2px rgba(0,0,0,.075);">
  <img class="col-sm-12 col-md-12 col-lg-12 thumbnail" data-index="<?php echo $index ?>" src="{{ $poster }}" width="100%" style="margin-top:20px;cursor:hand;cursor:pointer">

  <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $title }}}</h5>
  <div class="col-sm-12 col-md-12 col-lg-12">{{{ $duration }}}</div>
  @if (isset($isNsfw) && $isNsfw === true)
  <div class="col-sm-12 col-md-12 col-lg-12"><strong>* SEKUSHI *</strong></div>
  @endif
  <ul class="col-sm-12 col-md-12 col-lg-12" style="list-style:none">
    <li><a class="play" data-index="<?php echo $index ?>" href="#">{{ Lang::get('videos.single.play') }}</a></li>
    @if (isset($username))
      <div class="col-sm-12 col-md-12 col-lg-12">{{ Lang::get('videos.single.curatedby') }} {{{ $username }}}</div>
    @else
      <li><a class="edit" href="<?php $url = "/me/videos/{$id}/edit"; echo URL::secure($url) ?>">{{ Lang::get('videos.single.editvideo') }}</a></li>
      <li><a class="tags" href="<?php $url = "/me/videos/{$id}/tags/edit"; echo URL::secure($url) ?>">{{ Lang::get('videos.single.edittags') }}</a></li>
      <li><a class="delete" href="<?php $url = "/me/videos/{$id}/delete"; echo URL::secure($url) ?>">{{ Lang::get('videos.single.deletevideo') }}</a></li>
    @endif
  </ul>
</div>