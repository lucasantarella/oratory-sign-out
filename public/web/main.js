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

    bootstrap: 'vendor/bootstrap/js/bootstrap',
    tether: 'vendor/tether/js/tether',

    gapijs: "https://apis.google.com/js/platform",
    gapi: "gapi",

    // require plugins
    async: 'vendor/require-plugins/dist/async',
    // font: 'vendor/require-plugins/dist/font',
    // goog: 'vendor/require-plugins/dist/goog',
    // image: 'vendor/require-plugins/dist/image',
    // json: 'vendor/require-plugins/dist/json',
    // noext: 'vendor/require-plugins/dist/noext',
    // mdown: 'vendor/require-plugins/dist/mdown',
    // propertyParser : 'vendor/require-plugins/lib/propertyParser',
    // markdownConverter : 'vendor/require-plugins/lib/Markdown.Converter',

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
    'tether': ['jquery'],
    'bootstrap': ['jquery', 'tether'],
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
  'tether',
  'bootstrap',
  'css!styles/bootstrap.css',
  'css!vendor/font-awesome/scss/font-awesome.css',
  'css!vendor/tether/css/tether.min.css',
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