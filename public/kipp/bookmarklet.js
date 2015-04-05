(function () {

  // Load jQuery if not found equal or above specified version.
  var v = '1.9.1',
      root = window.location.href.indexOf('localhost') != -1 ? 'mypleasure.local' : 'still-mountain-6425.herokuapp.com';

  if (window.jQuery === undefined || window.jQuery.fn.jQuery < v) {
    var done = false,
        script = document.createElement('script');

    script.async = true;
    script.defer = true;
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

  // Start bookmarklet.
  function initBookmarklet() {
    window.mypleasure = window.mypleasure ? window.mypleasure : {};
    window.mypleasure.isOpen = false;

    var $ = window.jQuery,
        css = ['https://' + root + '/kipp/kipp.css'],
        js = ['https://' + root + '/kipp/kipp.js'],
        $spinner = null;

    // Get cross iframe message to close final window.
    function onMessage(e) {
      if ((e.origin == 'https://' + root) && e.data.event && e.data.event == 'done') {
        window.mypleasure.bookmarklet.stop();
      }
    };

    // Starter. Invoked when all sources are loaded.
    function sendOnMission() {
      try {
        window.onmessage = onMessage;
        window.mypleasure.bookmarklet.start();
      } catch (err) {
        window.mypleasure.hideLoader();
        console.log(err.stack);
      }
    }

    // Attach CSS styles.
    function getStyles(files) {
      $.each(files, function iter(i, val) {
        $('<link>').attr({ href: val, rel: 'stylesheet' }).appendTo('head');
        if (i === files.length - 1) {
          sendOnMission();
        }
      });
    }

    // Attach scripts.
    function getScripts(files) {
      if (files.length === 0) {
        getStyles(css);
        return false;
      }

      $.getScript(files[0])
       .done(function done() {
          getScripts(files.slice(1));
       });
    }

    // Show window spinner.
    window.mypleasure.showLoader = function () {

      $spinner = $('<div style="position:absolute;text-align:center;display:block;top:50%;left:50%;width:42px;height:42px;margin-left:-20px;margin-right:-20px;position:absolute;background:black;border:1px solid white;-webkit-border-radius:30px;-moz-border-radius:30px;-ms-border-radius:30px;border-radius:30px;" title="0"><svg version="1.1" id="loader-1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="40px" height="40px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve"><path opacity="0.4" fill="#000" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946 s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634 c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"/><path fill="#000" d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0 C22.32,8.481,24.301,9.057,26.013,10.047z"><animateTransform attributeType="xml" attributeName="transform" type="rotate" from="0 20 20" to="360 20 20" dur="0.5s" repeatCount="indefinite"/></path></svg></div>');
      $('body').append($spinner);
    }

    // Hide spinner.
    window.mypleasure.hideLoader = function () {
      $spinner.fadeOut('slow', function r() {
        $spinner.remove();
      })
    }

    // Off we go...
    function start() {
      if (window.mypleasure.isOpen) return;
      window.mypleasure.isOpen = true;
      window.mypleasure.showLoader();
      getScripts(js);
    }

    start();
    return false;
  }

})();