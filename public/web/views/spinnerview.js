// Filename: /path/to/file.js

define([
  'underscore',
  'backbone',
  'marionette',
  'css!styles/spinner.css'
], function (_, Backbone, Marionette) {

  return Marionette.View.extend({

    tagName: 'div',

    template: _.template('' +
      '<div class="spinner">' +
      '<div class="rect1"></div>' +
      '<div class="rect2"></div>' +
      '<div class="rect3"></div>' +
      '<div class="rect4"></div>' +
      '<div class="rect5"></div>' +
      '</div>' +
      ''),

  });

});
