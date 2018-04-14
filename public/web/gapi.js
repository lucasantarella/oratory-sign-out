define('gapi', {
  load: function (name, req, onload, config) {
    if (config.isBuild)
      onload();
    else {
      req(['gapijs'], function (gapi) {
        gapi.load(name, function () {
          onload(gapi[name]);
        });
      });
    }
  }
});