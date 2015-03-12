(function () {
  var root = window.location.href.indexOf('localhost') != -1 ? 'mypleasure.local' : 'still-mountain-6425.herokuapp.com';

  if (!window.KIPP) {
    console.log("[mypleasu.re KIPP] Couldn't find namespace. I give up.");
    return false;
  }

  var $body, $kipp, $kippElementContainer, openedWindow, oldOverflowValue;

  function closeKIPP(e) {
    e.preventDefault();
    $(e).unbind('click', closeKIPP);
    KIPP.close();
  }

  function addBtnHandler(e) {
    var embedUrl = $('iframe', $('#' + e.target.rel)).attr('src'),
        generator = e.data.generator,
        url = generator(embedUrl);

    openSite(url);
  }

  function openSite(url) {
    var features = 'menubar=no,location=no,resizable=no,scrollbars=no,status=no,left=50,top=50,width=640,height=480';
    openedWindow = window.open(
      'https://' + root + '/me/videos/create?u=' + url,
      'MPCase',
      features
    );
  }

  KIPP.ready = true;

  KIPP.open = function () {
    if (KIPP.active) {
      console.log("[mypleasu.re KIPP] I'm already active.");
      return false;
    }

    KIPP.active = true;

    if (!$body) $body = $('body');
    oldOverflowValue = $body.css('overflow');
    $body.css('overflow', 'hidden');

    console.log("[mypleasu.re KIPP] Now starting...");

    var kipp = document.createElement('div');
    kipp.id = 'mp-kipp';
    $kipp = $(kipp);

    document.getElementsByTagName('body')[0].appendChild(kipp);

    var overlay = document.createElement('div');
    overlay.className += overlay.className ? ' mp-kipp-overlay' : 'mp-kipp-overlay';
    kipp.appendChild(overlay);

    var elementContainer = document.createElement('div');
    elementContainer.className += elementContainer ? ' mp-kipp-elm-container' : 'mp-kipp-elm-container';
    kipp.appendChild(elementContainer);
    $kippElementContainer = $(elementContainer);

    var closeBtn = document.createElement('a');
    closeBtn.className += closeBtn.className ? ' mp-kipp-close-btn' : 'mp-kipp-close-btn';
    closeBtn.innerHTML = '&times;';
    closeBtn.href = '#';
    kipp.appendChild(closeBtn);

    var $closeBtn = $(closeBtn);
    $closeBtn.bind('click', closeKIPP);

    $(document).ready(KIPP.findPatterns());
  };

  KIPP.close = function () {
    console.log("[mypleasu.re KIPP] I'm out. See you next time!");
    KIPP.active = false;
    $kipp.fadeOut(function out() {
      $kipp.remove();
      $kipp = null;
      $kippElementContainer = null;
      $body.css('overflow', oldOverflowValue);
      $body = null;
    });
  };

  KIPP.findPatterns = function () {
    if (!KIPP.patterns) return;

    $.each(KIPP.patterns, function iter(i, pattern) {
      var $search = $(pattern.tag),
          index = 0;
      if ($search.length > 0) {
        $.each($search, function iter(i, elm) {
          KIPP.addElement(elm, pattern.generator, index);
          index++;
        });
      }
    });
  };

  KIPP.addElement = function(elm, generator, i) {
    var elmContainer = document.createElement('div');
    elmContainer.className += elmContainer.className ? ' mp-kipp-elm' : 'mp-kipp-elm';

    var id = 'mp-kipp-elm-' + i;
    elmContainer.id = id;

    var $elm = $(elm),
        $elmContainer = $(elmContainer);

    $kippElementContainer.append(elmContainer);
    $elm.clone().appendTo(elmContainer);

    $('iframe', $elmContainer).attr('width', 400).attr('height', 'auto');

    var $addBtn = $('<a href="#" class="mp-kipp-add-btn" rel="' + id + '">Ajouter cette vidéo</a>');
    $elmContainer.append($addBtn);
    $addBtn.bind('click', { generator: generator }, addBtnHandler);
  };

  KIPP.patterns = [
    {
      name: 'youtube',
      tag: "iframe[src*='youtube.com/embed']",
      generator: function (embedUrl) {
        var id = embedUrl.substring(embedUrl.lastIndexOf('/') + 1);
        return 'https://www.youtube.com/watch?v=' + id;
      }
    }
  ]
})();