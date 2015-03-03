<div class="modal fade" id="player" tabindex="-1" role="dialog" aria-labelledby="player" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="player-label">{{ Lang::get('videos.player.title') }}</h4>
        </div>
        <div class="modal-body">
            <div id="embed-body"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary prev-btn"><<</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ Lang::get('videos.player.close') }}</button>
            <button type="button" class="btn btn-primary next-btn">>></button>
        </div>
    </div>
  </div>

  <script>
  $(function() {
    var $modal = $('#player'),
        $label = $('#player-label'),
        $body = $('#embed-body'),
        $playBtns = $('.play'),
        $thumbnails = $('.thumbnail'),
        $prevBtn = $('.prev-btn'),
        $nextBtn = $('.next-btn'),
        embeds = [];
        currentIndex = 0,
        iframe = null;

    function init() {
      var $videos = $('.video');

      _.each($videos, function iter(video) {
        embeds.push($(video).attr('data-video'));
      });

      $playBtns.bind('click', openModal);
      $thumbnails.bind('click', openModal);
      $prevBtn.bind('click', prevVideo);
      $nextBtn.bind('click', nextVideo);
      $modal.bind('hide.bs.modal', closeModal);
    }

    function makeIframe(embed) {
      var iframe = '<iframe width="565" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' + embed + '"></iframe>';
      $body.html(iframe);
    }

    function openModal(e) {
      e.preventDefault();

      currentIndex = parseInt($(e.target).attr('data-index'));
      embed = embeds[currentIndex];
      makeIframe(embed);

      var options = {
        'backdrop': true,
        'keyboard': true
      };
      $modal.modal(options);
    }

    function closeModal(e) {
      iframe = null;
      $body.html('');
    }

    function prevVideo() {
      var previous = currentIndex === 0 ? embeds.length - 1 : currentIndex - 1;
      embed = embeds[previous];
      makeIframe(embed);
      currentIndex = previous;
    }

    function nextVideo() {
      var next = currentIndex === embeds.length - 1 ? 0 : currentIndex + 1;
      embed = embeds[next];
      makeIframe(embed);
      currentIndex = next;
    }

    init();
  });
  </script>