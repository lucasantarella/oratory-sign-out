// Filename: /collections/locations.js

define([
  'backbone',
  'models/location',
], function (Backbone, Location) {
  return Backbone.Collection.extend({

    model: Location,

  });
});