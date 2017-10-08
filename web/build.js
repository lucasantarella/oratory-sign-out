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
        marionette: 'vendor/backbone-marionette/backbone.marionette.min',
        text: 'vendor/require-text/text.min',

        bootstrap: 'vendor/bootstrap/js/bootstrap.min',
        tether: 'vendor/tether/js/tether.min',

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
    map: {
        '*': {
            css: 'vendor/require-css/css'
        }
    },
    name: './main',
    out: 'main-built.js'
});