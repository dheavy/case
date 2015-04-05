(function mp(window, $) {

  /**************************************************************
   *  KIPP - mypleasu.re bookmarklet.                           *
   **************************************************************/
  window.mypleasure.bookmarklet = (function() {

    var kipp = null,
        KIPP = null,
        $window = $(window),
        $body = $('body');

    KIPP = function () {
      this.version = '0.2.0';

      // Boolean flags to control flow.
      this.isActive = false;
      this.hasBuiltUI = false;
      this.hasFoundSomething = false;

      // References jQuery-wrapped elements used by the app.
      $body = null;
      this.$overlay = null;
      this.$container = null;
      this.$thumbnails = null;
      this.$collector = null;
      this.$closeBtn = null;

      // Store old CSS values we'll be changing along the way.
      this.oldBodyOverflowValue = '';
      this.oldBodyPosition = '';
      this.oldBodyWidth = '';
      this.oldBodyHeight = '';

      // Counter incremented on each created thumbnail, used to create IDs.
      this.uiIndex = 0;

      // Stores buttons listening for events to unbind them on garbage collection.
      this.eventListeningButtons = [];

      // Root URL for CASE, the website.
      this.CASE = window.location.href.indexOf('localhost') != -1 ? 'https://mypleasure.local' : 'https://still-mountain-6425.herokuapp.com';

      return this;
    };

    /**
     * Start KIPP.
     * Let it look for pattern in the source code of the current page.
     *
     * @return {KIPP}
     */
    KIPP.prototype.start = function () {
      console.log('[KIPP] Start.');

      // Singleton.
      if (kipp.isActive) return;
      kipp.isActive = true;

      // Go look for videos.
      kipp.findPatterns(sites);

      return kipp;
    };

    /**
     * Stop KIPP.
     * Invoke methods for UI removal and freeing memory.
     *
     * @return {KIPP}
     */
    KIPP.prototype.stop = function () {
      console.log('[KIPP] Stop.');

      $window.off('resize', kipp.resizeOverlay);
      kipp.$closeBtn.off('touchstart click', kipp.stop);

      $body.css('overflow', kipp.oldBodyOverflowValue);
      $body.css('position', kipp.oldBodyPosition);
      $body.css('width', kipp.oldBodyWidth);
      $body.css('height', kipp.oldBodyHeight);

      kipp.close();
      return kipp;
    };

    /**
     * Close KIPP.
     * Reset state variables.
     *
     * @return {KIPP}
     */
    KIPP.prototype.close = function () {
      console.log('[KIPP] Close.');

      if (this.$container) this.$container.remove();

      // Reset flags.
      this.hasBuiltUI = false;
      this.isActive = false;
      this.hasFoundSomething = false;
      window.mypleasure.isOpen = false;

      // Free jQuery objects from memory.
      $body = null;
      this.$overlay = null;
      this.$container = null;
      this.$thumbnails = null;
      this.$collector = null;
      this.$closeBtn = null;

      return kipp;
    };

    /**
     * Inform that no video has been found on the page, and close KIPP.
     *
     * @return {KIPP}
     */
    KIPP.prototype.fail = function () {
      alert("[mypleasu.re] Je n'arrive pas à trouver de vidéo sur cette page ! Désolé !");
      window.mypleasure.hideLoader();
      kipp.close();
    };

    /**
     * Construct the UI.
     *
     * @return {KIPP}
     */
    KIPP.prototype.buildUI = function (addTnContainer) {
      console.log('[KIPP] Build UI.');

      addTnContainer = addTnContainer ? addTnContainer : true;

      // Singleton.
      if (kipp.hasBuiltUI) return;
      kipp.hasBuiltUI = true;

      // Endow body with version number.
      // Store body's original value for 'overflow' and apply a new one.
      $body = $('body');
      $body.attr('data-mypleasure-bookmarklet-installed', kipp.version);
      kipp.oldBodyOverflowValue = $body.css('overflow');
      kipp.oldBodyPosition = $body.css('position');
      kipp.oldBodyWidth = $body.css('width');
      kipp.oldBodyHeight = $body.css('height');
      $body.css({
        'overflow': 'hidden',
        'position': 'fixed',
        'width': '100%',
        'height': '100%'
      });

      // Build main container.
      kipp.$container = $('<div id="mp-kipp"></div>');
      $body.append(kipp.$container);

      // Determine maximum z-index to apply it to the container.
      var maxZ = Math.max.apply(null, $.map($('body > *'), function apply(e) {
            var $e = $(e);
            if ($e.css('position') == 'absolute') {
              return parseInt($e.css('z-index')) || 1;
            }
          }));
      kipp.$container.css('z-index', maxZ);

      // Add overlay. Ensure it fits and remains so.
      kipp.$overlay = $('<div class="mp-kipp-overlay"></div>');
      kipp.$container.append(kipp.$overlay);
      $window.on('resize', kipp.resizeOverlay);

      // Thumbnails container.
      if (addTnContainer) {
        kipp.$thumbnails = $('<div class="mp-kipp-tn-container"></div>');
        kipp.$container.append(kipp.$thumbnails);
      }

      // Container for final input, where user effectively connects a video.
      kipp.$collector = $('<div class="mp-kipp-finalize-container"></div>');
      kipp.$container.append(kipp.$collector);

      // Close button.
      kipp.$closeBtn = $('<a href="#" class="mp-kipp-close-btn">&times;</a>');
      kipp.$closeBtn.on('touchstart click', kipp.stop);
      kipp.$container.append(kipp.$closeBtn);

      // Remove loader anim, if visible.
      window.mypleasure.hideLoader();

      return kipp;
    };

    /**
     * Cycles through the list to find code pattern and possibly curate the matching videos.
     *
     * @param  {Array} sites  The list of sites with relevant data on code formatting.
     * @return {KIPP|boolean} Returns false to break out of $.each loop when something is found.
     */
    KIPP.prototype.findPatterns = function (sites) {
      console.log('[KIPP] Find patterns.');

      var location = window.location.href,
          self = kipp;

      // Loop through all patterns from defined sites.
      $.each(sites, function iter(i, pattern) {
        console.log('[KIPP] - ' + pattern.name);

        var cases = pattern.cases;
        $.each(cases, function iter(i, c) {

          // If current location matches a pattern, it's a go.
          if (c.urlPattern.test(location) && c.direct) {
            console.log('[KIPP] --- found!');
            self.hasFoundSomething = true;
            self.finalize(location);
            return false;
          }

          // Otherwise, try looking for it in the DOM.
          if (!self.hasFoundSomething) {
            self.searchDOM(pattern.name, c);
          }
        });
      });

      if (!kipp.hasFoundSomething) kipp.fail();
      return kipp;
    };

    /**
     * Traverse the DOM to find code patterns.
     *
     * @param  {string} name       The site name.
     * @param  {Object} searchCase Contains regex and other data to base DOM analysis on.
     * @return {KIPP|boolean} Returns false to break out of $.each loop when something is found.
     */
    KIPP.prototype.searchDOM = function (name, searchCase) {
      console.log("[KIPP] -- search DOM for " + name + " with selector '" + searchCase.selector + "'.");

      var $search = $(searchCase.selector),
          self = kipp;

      if ($search.length > 0) {
        kipp.hasFoundSomething = true;
        $.each($search, function iter(i, elm) {
          self.scrapeElement(elm, searchCase.urlGenerator, searchCase.thumbsStrategy, kipp.uiIndex);
          kipp.uiIndex++;
          return false;
        });
      }

      return kipp;
    };

    /**
     * Scrape a found video element to display it to the user.
     *
     * @param  {string}   element        The DOM element to scrape.
     * @param  {Function} urlGenerator   Generates the actual video URL from its embed code.
     * @param  {Function} thumbsStrategy Generates, from its embed code, the video thumbnails presented to the user.
     * @param  {integer}  index          A UID used when adding thumbnails in the DOM for retrieval purposes.
     * @return {KIPP}
     */
    KIPP.prototype.scrapeElement = function (element, urlGenerator, thumbsStrategy, index) {
      console.log('[KIPP] Add element.');

      // Build UI, if needed.
      if (!this.hasBuiltUI) {
        kipp.buildUI();
      }

      // Create UI for the element.
      var id = 'mp-kipp-elm-' + index,
          $element = $(element),
          $elementContainer = $('<div class="mp-kipp-elm" id="' + id + '"></div>'),
          $addBtn = $('<a href="#" class="mp-kipp-add-btn" rel="' + id + '">Ajouter cette vidéo</a>'),
          self = kipp;

      // Append to the view container.
      kipp.$thumbnails.append($elementContainer);
      thumbsStrategy($element, $elementContainer);
      $elementContainer.append($addBtn);

      // Adjust the layout.
      $('iframe', $elementContainer).attr('width', 400).attr('height', 'auto');

      // Add and prepare button for collecting video.
      $addBtn.on('click', { urlGenerator: urlGenerator }, self.onAdd);

      // Add button to a list of element listing to events.
      // We'll unbind all events from elements in the list when we close the bookmarklet.
      kipp.eventListeningButtons.push($addBtn);

      return kipp;
    };

    /**
     * Event handler invoked when "add" button is clicked.
     * Finalizes acquisition of the related video.
     *
     * @param  {jQuery.Event} e  The event object passed during the process.
     * @return {KIPP}
     */
    KIPP.prototype.onAdd = function (e) {
      var embedURL = $('iframe', $('#' + e.target.rel)).attr('src'),
          urlGenerator = e.data.urlGenerator,
          url = urlGenerator(embedURL);


      kipp.finalize(url);

      return kipp;
    };

    /**
     * Resize the background overlay to fit the window.
     *
     * @return {KIPP}
     */
    KIPP.prototype.resizeOverlay = function () {
      if ($window && kipp.$overlay) {
        kipp.$overlay.width($window.width());
        kipp.$overlay.height($window.height());
      }

      return kipp;
    };

    /**
     * Finalize the acquisition of the video by invoking the relevant page from CASE in an iframe.
     *
     * @param  {string} url  The video URL to passed to CASE.
     * @return {KIPP}
     */
    KIPP.prototype.finalize = function (url) {
      // Unbind obsolete event listeners.
      $.each(kipp.eventListeningButtons, function iter(i, b) {
        $(b).unbind();
      });

      // Build UI, minus thumbnails container.
      kipp.buildUI(false);

      // Create final view with iFrame from CASE.
      var $iframe = $('<iframe src="' + this.CASE + '/me/videos/create?u=' + url + '" width="100%" height="100%" frameborder="0"></iframe>');
      kipp.$collector.append($iframe).css('display', 'block');

      return kipp;
    };

    // Create instance of KIPP.
    kipp = new KIPP();

    /**
     * List of sites and code pattern to find videos.
     *
     * @type {Array}
     */
    var sites = [
      {
        name: 'youtube',

        // Youtube: all cases.
        cases: [
          {
            urlPattern: /www\.youtube\.com\/watch\?v=/,
            direct: true,
            selector: 'iframe[src*="youtube.com/embed"]',
            thumbsStrategy: function ($target, $container) {
              return $target.clone().appendTo($container);
            },
            urlGenerator: function (embedURL) {
              var id = embedURL.substring(embedURL.lastIndexOf('/') + 1);
              return 'https://www.youtube.com/watch?v=' + id;
            }
          }
        ]
      },

      {
        name: 'vimeo',

        // Vimeo: based on url.
        cases: [
          {
            urlPattern: /vimeo\.com\/(\d+)/,
            direct: true,
            selector: 'iframe[src*="player.vimeo.com/video/"]',
            thumbsStrategy: function ($target, $container) {
              return $target.clone().appendTo($container);
            },
            urlGenerator: function (embedURL) {
              var matches = /(\/)(\d+)/.exec(embedURL), id;
              if (matches[2]) {
                id = matches[2];
                return 'https://vimeo.com/' + id;
              }
            }
          },

          // Vimeo: listings of video (e.g. homepage).
          {
            urlPattern: /vimeo\.com(\/){0}(?! \d+)/gi,
            direct: false,
            selector: '.faux_player',
            thumbsStrategy: function ($target, $container) {
              var id = $target.attr('data-clip-id'),
                  $iframe = $('<iframe src="https://player.vimeo.com/video/' + id + '?title=0&amp;byline=0&amp;portrait=0" width="400" height="auto" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="" kwframeid="1"></iframe>');

              return $iframe.appendTo($container);
            },
            urlGenerator: function (embedURL) {
              var matches = /(\/)(\d+)/.exec(embedURL), id;
              if (matches[2]) {
                id = matches[2];
                return 'https://vimeo.com/' + id;
              }
            }
          },

          // Vimeo: hero video display.
          {
            urlPattern: /vimeo\.com/,
            direct: false,
            selector: '#video',
            thumbsStrategy: function($target, $container) {
              var id = $('.player_container', $target).attr('id').substr(5),
                  $iframe = $('<iframe src="https://player.vimeo.com/video/' + id + '?title=0&amp;byline=0&amp;portrait=0" width="400" height="auto" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="" kwframeid="1"></iframe>');

              return $iframe.appendTo($container);
            },
            urlGenerator: function(embedURL) {
              var matches = /(\/)(\d+)/.exec(embedURL), id;
              if (matches[2]) {
                id = matches[2];
                return 'https://vimeo.com/' + id;
              }
            }
          },

          // Vimeo: couchmode.
          {
            urlPattern: /vimeo\.com/,
            direct: false,
            selector: '#big_screen',
            thumbsStrategy: function($target, $container) {
              var videoSrc = $('video', $target).attr('src'),
                  id = videoSrc.substring(videoSrc.indexOf('=') + 1, videoSrc.indexOf('_')),
                  $iframe = $('<iframe src="https://player.vimeo.com/video/' + id + '?title=0&amp;byline=0&amp;portrait=0" width="400" height="auto" frameborder="0" webkitallowfullscreen="" mozallowfullscreen="" allowfullscreen="" kwframeid="1"></iframe>');

              return $iframe.appendTo($container);
            },
            urlGenerator: function(embedURL) {
              var matches = /(\/)(\d+)/.exec(embedURL), id;
              if (matches[2]) {
                id = matches[2];
                return 'https://vimeo.com/' + id;
              }
            }
          }
        ]
      },
      {
        name: 'dailymotion',
        cases: [

          // Dailymotion: based on URL.
          {
            urlPattern: /dailymotion\.com\/video/,
            direct: true,
            selector: '#content.fluid[itemtype="http://schema.org/VideoObject"]',
            thumbsStrategy: function ($target, $container) {
              var link = $('link[itemprop="embedURL"]', $target).attr('href');
                  id = link.substring(link.lastIndexOf('/') + 1),
                  $iframe = $('<iframe frameborder="0" width="400" height="auto" src="//www.dailymotion.com/embed/video/' + id + '" allowfullscreen></iframe>');

              return $iframe.appendTo($container);
            },
            urlGenerator: function (embedURL) {
              return 'https://www.dailymotion.com/video/' + embedURL.substring(embedURL.lastIndexOf('/') + 1);
            }
          },

          {
            urlPattern: /dailymotion\.com/,
            direct: false,
            selector: 'iframe[src*="//www.dailymotion.com/embed/video"]',
            thumbsStrategy: function ($target, $container) {
              var $iframe = $('<iframe frameborder="0" width="400" height="auto" src="' + $target.attr('src') + '" allowfullscreen></iframe>');
              return $iframe.appendTo($container);
            },
            urlGenerator: function (embedURL) {
              return 'https://www.dailymotion.com/video/' + embedURL.substring(embedURL.lastIndexOf('/') + 1);
            }
          }
        ]
      }
    ];

    /**
     * Public API.
     */
    return {
      start: kipp.start,
      stop: kipp.stop
    }
  })();

})(window, jQuery);