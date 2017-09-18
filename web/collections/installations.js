// Filename: /collections/installations.js

define([
  'backbone',
  'models/installation',
], function (Backbone, Installation) {
  return Backbone.Collection.extend({

    model: Installation,

    url: '/api/installations',

  });
});