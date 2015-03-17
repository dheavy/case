(function () {
  var root = window.location.href.indexOf('localhost') != -1 ? 'mypleasure.local' : 'still-mountain-6425.herokuapp.com';

  if (!window.KIPP) {
    console.log("[mypleasu.re KIPP] Couldn't find namespace. I give up.");
    return false;
  }

  var $window, $body, $kipp, $kippElementContainer, $finalizeContainer, openedWindow, oldOverflowValue,
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

    //openSite(url);
    KIPP.finalize(url);
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

  KIPP.addBtns = [];

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

    window.onmessage = function (e) {
      if (e.origin == 'https://' + root && e.data.event && e.data.event == 'done') {
        KIPP.close();
      }
    };

    $(KIPP.findPatterns());
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
    // Main container.
    $kipp = $('<div id="mp-kipp"></div>');
    $body.append($kipp);

    // Background overlay.
    var $overlay = $('<div class="mp-kipp-overlay"></div>');
    var maxZ = Math.max.apply(null, $.map($('body > *'), function (e, n) {
      var $e = $(e);
      if ($e.css('position') == 'absolute') {
        return parseInt($e.css('z-index')) || 1;
      }
    }));
    $kipp.css('z-index', maxZ).append($overlay);

    // Elements container, for videos found.
    $kippElementContainer = $('<div class="mp-kipp-elm-container"></div>');
    $kipp.append($kippElementContainer);

    // Container for final input.
    $finalizeContainer = $('<div class="mp-kipp-finalize-container"></div>');
    $kipp.append($finalizeContainer);

    // Close button.
    var $closeBtn = $('<a href="#" class="mp-kipp-close-btn">&times;</a>');
    $closeBtn.bind('touchstart click', closeKIPP);
    $kipp.append($closeBtn);

    // Flag after building is through.
    KIPP.hasBuiltUI = true;
  };

  KIPP.addElement = function(elm, generator, i) {
    if (!KIPP.hasBuiltUI)        KIPP.buildUI();
    if (!KIPP.hasFoundSomething) KIPP.hasFoundSomething = true;

    var $elm = $(elm),
        id = 'mp-kipp-elm-' + i,
        $elmContainer = $('<div class="mp-kipp-elm" id="' + id + '"></div>'),
        $addBtn = $('<a href="#" class="mp-kipp-add-btn" rel="' + id + '">Ajouter cette vidéo</a>');

    $kippElementContainer.append($elmContainer);
    $elm.clone().appendTo($elmContainer);

    $('iframe', $elmContainer).attr('width', 400).attr('height', 'auto');

    $elmContainer.append($addBtn);
    $addBtn.bind('click', { generator: generator }, addBtnHandler);
    KIPP.addBtns.push($addBtn);
  };

  KIPP.finalize = function(url) {
    $.each(KIPP.addBtns, function iter(i, b) {
      $(b).unbind();
    });

    $kippElementContainer.remove();

    var $iframe = $('<iframe src="https://' + root + '/me/videos/create?u=' + url + '" width="100%" height="100%" frameborder="0"></iframe>');
    $finalizeContainer.append($iframe).css('display', 'block');
  };

  KIPP.patterns = [
    {
      name: 'youtube',
      urlPattern: /www\.youtube\.com\/watch\?v=/,
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
      urlPattern: /vimeo\.com\/(\d+)/,
      isTricky: true,
      trickyUrlPattern: /vimeo\.com(\/){0}(?! \d+)/gi,
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
  ];
})();