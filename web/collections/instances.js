// Filename: /collections/instances.js

define([
  'backbone',
  'models/instance',
], function (Backbone, Instance) {
  return Backbone.Collection.extend({

    model: Instance,

    url: '/api/instances',

  });
});