// Filename: /views/AppView.js

define([
  'underscore',
  'marionette'
], function (_, Marionette) {
  return Marionette.View.extend({

    tagName: 'div',

    id: 'main-wrapper',

    template: _.template('' +
      '<main>' +
      '<div id="main">' +
      '</div>' +
      '</main>' +
      '<footer class="page-footer blue darken-4">' +
      ' <div class="container">' +
      '   <div class="row">' +
      '   © 2018 Copyright Luca Santarella' +
      '   </div>' +
      ' </div>' +
      '</footer>' +
      ''),

    regions: {

      main: {
        el: '#main',
        replaceElement: true
      }

    },

  });
});
