(function () {

  var v = '1.3.2',
      root = window.location.href.indexOf('localhost') != -1 ? 'mypleasure.local' : 'still-mountain-6425.herokuapp.com';

  if (window.jQuery === undefined || window.jQuery.fn.jQuery < v) {
    var done = false,
        script = document.createElement('script');

    script.src = '//ajax.googleapis.com/ajax/libs/jquery/' + v + '/jquery.min.js';
    script.onload = script.onreadystatechange = function () {
      if (!done && (!this.readyState || this.readyState == 'loaded' || this.readyState == 'complete')) {
        done = true;
        initBookmarklet();
      }
    };

    document.getElementsByTagName('head')[0].appendChild(script);
  } else {
    initBookmarklet();
  }

  function initBookmarklet() {
    window.KIPP = window.KIPP ? window.KIPP : {};

    var css = ['https://' + root + '/kipp/kipp.css'],
        js = ['https://' + root + '/kipp/kipp.js'];

    if (KIPP.true) {
      sendOnMission();
      return false;
    }

    function sendOnMission() {
      KIPP.open();
    }

    function getStyles(files) {
      try {
        $.each(files, function iter(i, val) {
          $('<link>').attr({ href: val, rel: 'stylesheet' }).appendTo('head');
          if (i === files.length - 1) {
            sendOnMission();
          }
        });
      } catch (e) {
        console.log("[mypleasu.re KIPP] Error loading of the style files. I give up.");
        return false;
      }
    }

    function getScripts(files) {
      if (files.length === 0) {
        getStyles(css);
        return false;
      }

      $.getScript(files[0])
       .done(function done() {
          getScripts(files.slice(1));
       })
       .fail(function fail() {
          console.log("[mypleasu.re KIPP] Error loading one of the script files. I give up.");
          return false;
       });
    }

    getScripts(js);
  }

})();