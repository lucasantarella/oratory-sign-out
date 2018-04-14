require.config({
  baseUrl: './',
  paths: {
    vendor: './vendor',

    jquery: 'vendor/jquery/jquery',
    underscore: 'vendor/lodash/lodash.min',
    lodash: 'vendor/lodash/lodash.min',
    backbone: 'vendor/backbone/backbone',
    'backbone.radio': 'vendor/backbone-radio/backbone.radio',
    'backbone.paginator': 'vendor/backbone-paginator/backbone.paginator',
    'backbone.localStorage': 'vendor/backbone-localstorage/backbone.localStorage',
    marionette: 'vendor/backbone-marionette/backbone.marionette',
    cookie: 'vendor/cookie/cookie.min',
    text: 'vendor/require-text/text',
    jwtdecode: 'vendor/jwt-decode/jwt-decode',
    pace: 'vendor/pace/pace.min',

    gapijs: "https://apis.google.com/js/platform",
    gapi: "gapi",

    // require plugins
    async: 'vendor/require-plugins/async',
    font: 'vendor/require-plugins/font',
    goog: 'vendor/require-plugins/goog',
    image: 'vendor/require-plugins/image',
    json: 'vendor/require-plugins/json',
    noext: 'vendor/require-plugins/noext',
    mdown: 'vendor/require-plugins/mdown',
    propertyParser: 'vendor/require-plugins/propertyParser',

    layouts: './layouts',
    app: './app',
    modules: './modules',
    controllers: './controllers',
    models: './models',
    collections: './collections',
    views: './views',
    styles: './styles'
  },
  shim: {
    /* Set bootstrap dependencies (just jQuery) */
    'gapijs': {
      'exports': 'gapi'
    }
  },
  map: {
    '*': {
      css: 'vendor/require-css/css' // Or whatever the path to require-css is
    }
  }
});

require([
  'jquery',
  'backbone',
  'app',
  'pace',
  'css!styles/main.css'
], function ($, Backbone, App, pace) {

  pace.start({
    document: true,
  });

  $(document).ajaxStart(function () {
    pace.restart();
  });

  // Init the app
  let app = new App();

  app.setupSession()

});