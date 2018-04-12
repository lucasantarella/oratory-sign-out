({
  baseUrl: './',
  paths: {
    vendor: './vendor',

    jquery: 'vendor/jquery/jquery.min',
    underscore: 'vendor/lodash/lodash.min',
    lodash: 'vendor/lodash/lodash.min',
    backbone: 'vendor/backbone/backbone.min',
    'backbone.radio': 'vendor/backbone-radio/backbone.radio.min',
    'backbone.paginator': 'vendor/backbone-paginator/backbone.paginator.min',
    'backbone.localStorage': 'vendor/backbone-localstorage/backbone.localStorage.min',
    marionette: 'vendor/backbone-marionette/backbone.marionette.min',
    cookie: 'vendor/cookie/cookie.min',
    text: 'vendor/require-text/text.min',
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
    propertyParser : 'vendor/require-plugins/propertyParser',
    
    css: 'vendor/require-css/css',
    'css-builder': 'vendor/require-css/css-builder',
    'normalize': 'vendor/require-css/normalize',

    layouts: './layouts',
    app: './app',
    modules: './modules',
    controllers: './controllers',
    models: './models',
    collections: './collections',
    views: './views',
    styles: './styles'
  },
  name: './main',
  out: 'main-built.js',
});