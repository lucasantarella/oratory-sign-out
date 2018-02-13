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
        marionette: 'vendor/backbone-marionette/backbone.marionette',
        text: 'vendor/require-text/text',

        bootstrap: 'vendor/bootstrap/js/bootstrap',
        tether: 'vendor/tether/js/tether',

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
    },
    map: {
        '*': {
            css: 'vendor/require-css/css' // Or whatever the path to require-css is
        }
    }
});

require([
    'backbone',
    'app',
    'tether',
    'bootstrap',
    'css!styles/bootstrap.css',
    'css!vendor/font-awesome/scss/font-awesome.css',
    'css!vendor/tether/css/tether.min.css',
    'css!styles/main.css'
], function (Backbone, App) {
    // Init the app
    var app = new App();

    // Start the app
    app.start();
});
