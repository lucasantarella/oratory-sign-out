define('gapi', {
    load: function (name, req, onload, config) {
        req(['gapijs'], function (gapi) {
            gapi.load(name, function () {
                onload(gapi[name]);
            });
        });
    }
});