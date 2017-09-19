// Filename: /views/AppView.js

define([
    'underscore',
    'marionette'
], function (_, Marionette) {
    return Marionette.View.extend({

        tagName: 'div',

        id: 'main-wrapper',

        template: _.template('' +
            '<div class="container no-gutters-xs no-gutters-sm no-gutters-md no-gutters-lg no-gutters-xl" style="height: calc(10vh) !important;">' +
            '<div class="col-xs-12 p-3">' +
            '<h2 class="d-flex align-items-center align-content-center justify-left">' +
            '<img src="./img/oratory.png" width="40" height="40" class="mr-2"/>' +
            'Oratory Sign-Out' +
            '</h2>' +
            '</div>' +
            '</div>' +
            '<div id="main"></div>' +
            '<div class="footer w-100" style="height: calc(10vh) !important;"><div class="container no-gutters-xs no-gutters-sm no-gutters-md no-gutters-lg no-gutters-xl h-100 w-100 bg-faded py-3 d-flex align-items-center">' +
            '<div class="w-100 text-center text-muted"><p>Designed and Created by <a href="https://github.com/lucasantarella" class="btn btn-link p-0">@LucaSantarella</a>' +
            '</p>' +
            '</div>' +
            '</div>' +
            '</div>' +
            ''),

        regions: {

            main: {
                el: '#main',
                replaceElement: true
            }

        },

    });
});
