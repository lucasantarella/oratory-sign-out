// Filename: /views/instance/instancelocationitem.js

define([
  'underscore',
  'marionette'
], function (_, Marionette) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'list-group-item list-group-item-action',

    template: _.template('(<%- lat %>, <%- lon %>)')

  });
});
