(function () {
  var root = window.location.href.indexOf('localhost') != -1 ? 'mypleasure.local' : 'still-mountain-6425.herokuapp.com';

  if (!window.KIPP) {
    console.log("[mypleasu.re KIPP] Couldn't find namespace. I give up.");
    return false;
  }

  var $body, $kipp, $kippElementContainer, openedWindow, oldOverflowValue,
      index = 0;

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
      features,
      true
    );

    setTimeout(function popupBlockerCheck() {
      if (!openedWindow || openedWindow.closed || openedWindow.closed == "undefined" || openedWindow == "undefined" || parseInt(openedWindow.innerWidth) == 0 || openedWindow.document.documentElement.clientWidth != 150 || openedWindow.document.documentElement.clientHeight != 150) {
        openedWindow && openedWindow.close();
        if (KIPP && KIPP.isActive) {
          KIPP.isActive = false;
        }
        alert("La popup d'ajout de vidéo n'a pas réussi à s'ouvrir ! Autorisez-la en cliquant sur le bouton qui vient d'apparaître dans la barre d'adresse.");
      }
    }, 1000);
  }

  KIPP.isActive = false;
  KIPP.hasBuiltUI = false;
  KIPP.hasFoundSomething = false;

  KIPP.open = function () {
    if (KIPP.isActive) {
      console.log("[mypleasu.re KIPP] I'm already active.");
      return false;
    }

    KIPP.isActive = true;

    if (!$body) $body = $('body');
    oldOverflowValue = $body.css('overflow');
    $body.css('overflow', 'hidden');

    console.log("[mypleasu.re KIPP] Now starting...");

    $(document).ready(KIPP.findPatterns());
  };

  KIPP.close = function () {
    console.log("[mypleasu.re KIPP] I'm out. See you next time!");
    KIPP.isActive = false;

    if ($kipp) {
      $kipp.fadeOut(function out() {
        $kipp.remove();
        $kipp = null;
        $kippElementContainer = null;
        $body.css('overflow', oldOverflowValue);
        $body = null;
        KIPP.hasBuiltUI = false;
      });
    }
  };

  KIPP.findPatterns = function () {
    if (!KIPP.patterns) return;

    $.each(KIPP.patterns, function iter(i, pattern) {
      var location = window.location.href;

      // Check URL.
      if (pattern.urlPattern.test(location)) {
        KIPP.hasFoundSomething = true;
        openSite(window.location.href);
        return;
      }

      // Check URL for tricky ones.
      if (pattern.isTricky && pattern.trickyUrlPattern) {
        if (pattern.trickyUrlPattern.test(location)) {
          KIPP.hasFoundSomething = true;
          pattern.trickMethod();
          return;
        }
      }

      // Check DOM.
      KIPP.searchDOM(pattern);
    });

    if (!KIPP.hasFoundSomething) {
      alert("mypleasu.re — je n'arrive pas à trouver de vidéo sur cette page.");
      KIPP.close();
    }
  };

  KIPP.searchDOM = function (pattern) {
    console.log('[mypleasu.re KIPP] Searching DOM...');
    var $search = $(pattern.selector);

    console.log('[mypleasu.re KIPP] Searching selector: ' + pattern.selector);

    if ($search.length > 0) {
      KIPP.hasFoundSomething = true;
      $.each($search, function iter(i, elm) {
        KIPP.addElement(elm, pattern.generator, index);
        index++;
      });
    }
  };

  KIPP.buildUI = function () {
    var kipp = document.createElement('div');
    kipp.id = 'mp-kipp';
    $kipp = $(kipp);

    document.getElementsByTagName('body')[0].appendChild(kipp);

    var overlay = document.createElement('div');
    overlay.className += overlay.className ? ' mp-kipp-overlay' : 'mp-kipp-overlay';

    var maxZ = Math.max.apply(null,$.map($('body > *'), function(e,n){
    if($(e).css('position') == 'absolute')
      return parseInt($(e).css('z-index')) || 1 ;
    }));

    overlay.style = 'z-index:' + maxZ;
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

    KIPP.hasBuiltUI = true;
  };

  KIPP.addElement = function(elm, generator, i) {
    if (!KIPP.hasBuiltUI) KIPP.buildUI();
    if (!KIPP.hasFoundSomething) KIPP.hasFoundSomething = true;

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

  KIPP.getPatternsByName = function (name) {
    $.each(KIPP.patterns, function iter(i, p) {
      if (p.name == name) {
        return p;
      }
    });
    return null;
  };

  KIPP.patterns = [
    {
      name: 'youtube',
      urlPattern: /www.youtube.com\/watch?v=/,
      isTricky: false,
      trickyUrlPattern: null,
      selector: "iframe[src*='youtube.com/embed']",
      generator: function (embedUrl) {
        var id = embedUrl.substring(embedUrl.lastIndexOf('/') + 1);
        return 'https://www.youtube.com/watch?v=' + id;
      },
      trickMethod: null
    },
    {
      name: 'vimeo',
      urlPattern: /vimeo.com\/(\d+)/,
      isTricky: true,
      trickyUrlPattern: /vimeo.com(\/){0}(?! \d+)/gi,
      selector: "iframe[src*='player.vimeo.com/video/']",
      generator: function (embedUrl) {
        var matches = /(\/)(\d+)/.exec(embedUrl), id;
        if (matches[2]) {
          id = matches[2];
          return 'https://vimeo.com/' + id;
        }
      },
      trickMethod: function () {
        console.log("[mypleasu.re KIPP] Implementing trick method for Vimeo's front page.");

        this.selector = '.faux_player';

        var $search = $(this.selector);
        if ($search.length > 0) {
          var gen = KIPP.patterns[1].generator;

          KIPP.hasFoundSomething = true;
          $.each($search, function iter(i, elm) {
            var $elm = $(elm),
                element = $('<iframe src="https://player.vimeo.com/video/' + $elm.attr('data-clip-id') + '?title=0&byline=0&portrait=0" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe');
            KIPP.addElement(element, gen, index);
            index++;
          });
        }
      }
    }
  ]
})();