handleHeights = function () {
  var iframes = $("iframe"),
      len     = iframes.length,
      index,
      snippet,
      overflow,
      overflowData,
      height;

  for (index = 0; index < len; index++) {
    snippet      = $(iframes[index]).contents().find("#snippet"); // element for height measurement
    overflow     = snippet.css("overflow");
    overflowData = snippet.attr("data-default-overflow");
    if (overflowData !== undefined && overflowData !== "") {
      overflow = overflowData;
    }
    else {
      snippet.attr("data-default-overflow", overflow); //sets default after first check, so temp value does not get picked on resize iterations
    }
    snippet.css("overflow", "scroll"); // sets temp value for measuring
    height = snippet.get(0).offsetHeight;
    snippet.css("overflow", overflow); // sets styling value

    $(iframes[index]).height(height);
  }
};

var $preview       = $(".js-snippet-preview"),
    $previewSource = $preview.find("iframe"),
    //viewport
    $handleLeft    = $(".js-snippet-resize-handle-left"),
    $handleRight   = $(".js-snippet-resize-handle-right"),
    $resizeLength  = $(".js-resize-length"),
    //data
    snippetSource  = $(".js-snippet-source");

(function () {
  var windowWidth = $(".left").width(),
      width       = 1024;

  if ((width) + 100 > windowWidth) {
    width = (windowWidth - 100);
  }
  $preview.css('width', width);
  $resizeLength.css('width', parseInt(width / 2, 10));
})();

interact('.js-resize-length')
  .resizable(
    {
      edges:  {
        left:   ".js-snippet-resize-handle-right",
        right:  ".js-snippet-resize-handle-left",
        bottom: false,
        top:    false
      },
      onmove: function (e) {

        var width       = e.rect.width,
            windowWidth = $(".left").width();

        if (width < 160) {
          width = 160;
        }
        else if ((width * 2) + 100 > windowWidth) {
          width = (windowWidth - 100) / 2;
        }

        $preview
          .find(snippetSource)
          .addClass('resize-overlay');
        $preview[0].style.width      = (width * 2) + 'px';
        $resizeLength[0].style.width = width + 'px';
        handleHeights();
      },
      onend:  function () {
        $preview
          .find(snippetSource)
          .removeClass('resize-overlay');
        handleHeights();
      }
    }
  );

var editor_update = function (markup, json) {
  $("#display_holder").attr('srcdoc', markup);
  $("#json_holder pre").text(JSON.stringify(json, null, 2));
  $("#twig_holder").text(JSON.stringify(json, null, 2));
  updateDirectLink();
  $('#display_holder').load(
    function () {
      handleHeights();
    }
  );
};


function serialize(obj, prefix) {
  var str = [], p;
  for(p in obj) {
    if (obj.hasOwnProperty(p)) {
      var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
      str.push((v !== null && typeof v === "object") ?
        serialize(v, k) :
        encodeURIComponent(k) + "=" + encodeURIComponent(v));
    }
  }
  return str.join("&");
}
var updateDirectLink = function () {
  var url = window.location.href.replace(/\?.*/, '');

  var data = JSON.stringify(editor.getValue());

  // var jsonURL = serialize(editor.getValue());

  // console.log(jsonURL);

  // url += '?data=' + LZString.compressToBase64(JSON.stringify(editor.getValue()));

  var directUrl = '?data=' + btoa(data);
  var standalonePage = '/api/render/page' + '?template=' + JSON.parse(data).name + '&' + 'data=' + btoa(data);

  // console.log(standalonePage);
  // http://localhost:8000/api/render/page?template=card&data=eyJuYW1lIjoiY2FyZCIsImJhY2tncm91bmQiOiJibGFjayIsImJvZHkiOlt7Im5hbWUiOiJpbWFnZSIsInNyYyI6Ii9zcmMvaW1hZ2VzL3R1cnRsZS5qcGciLCJhbHQiOiJBIHR1cnRsZSIsInRpdGxlIjoiVGhpcyB0dXJ0bGUgaXMgZXhjaXRlZCEifSx7Im5hbWUiOiJxdW90ZSIsInF1b3RhdGlvbiI6IlllcywgZnJpZW5kcywgdGhlIG5ldyB0dWJvIGdpbnN1LiBXYS1ob28hIEl0IGRpY2VzLCBpdCBzbGljZXMsIGFuZCBpdCBtYWtlcyBGcmVuY2ggZnJpZXMgd2l0aCB0aHJlZSBkaWZmZXJlbnQgY3V0cy4gWWF5ISIsImF0dHJpYnV0aW9uIjp7Im5hbWUiOiJNaWNoYWVsYW5nZWxvIiwidGl0bGUiOiJBIFRlZW5hZ2UgTXV0YW50IE5pbmphIFR1cnRsZSJ9fV19
  // // var jsonURL = url + '?data=' + jsonURL;

  document.getElementById('direct_link').href = directUrl;
  document.getElementById('direct_link_new_window').href = standalonePage;
};

if (window.location.href.match('[?&]data=([^&]+)')) {
  try {



    // data.starting = '';
    // data.starting = JSON.parse(LZString.decompressFromBase64(window.location.href.match('[?&]data=([^&]+)')[1]));
    data.starting = JSON.parse(atob(window.location.href.match('[?&]data=([^&]+)')[1]));
  }
  catch (e) {
    // console.log(getParameterByName('data'));

    console.log('invalid starting data');
  }
}
if (data.starting.name) {
  JSONEditor.defaults.options.startval = data.starting;
}

// Initialize the editor with a JSON schema
var editor = new JSONEditor(
  document.getElementById('editor_holder'), {
    schema:            data.schema,
    theme:             'bootstrap3',
    iconlib:           'fontawesome4',
    keep_oneof_values: false
  }
);

JSONEditor.plugins.sceditor.emoticonsEnabled = false;
JSONEditor.plugins.ace.theme                 = 'twilight';

// Schema editor ajax debouncer.
//
// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
schemaEditorDebounce = function(func, wait, immediate) {
  var timeout;
  return function() {
    var context = this, args = arguments;
    var later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
};

// On changes to the editor UI, validate the JSON And update the preview render.
editor.on(
  'change', schemaEditorDebounce(function() {
    var json = editor.getValue();

    $.ajax(
      {
        url:         "/api/validate",
        method:      'POST',
        contentType: 'application/json',
        data:        JSON.stringify(json, null, 2)
      }
    ).success(
      function (response) {
        if ( response.trim() === "The supplied JSON validates against the schema." ) {
          $('.valid').removeClass('alert-danger').addClass('alert-success');
        } else if ( response.includes( "The supplied JSON validates against the schema." ) ) {
          $('.valid').removeClass('alert-danger').addClass('alert-warning');
        }

        $('.valid').html(response);
        $.ajax(
          {
            url:         "/api/render/page",
            method:      'POST',
            contentType: 'application/json',
            data:        JSON.stringify(json, null, 2)
          }
        ).done(
          function (markup) {
            editor_update(markup, json);
          }
        );

      }
    );
  }, 500)
);
