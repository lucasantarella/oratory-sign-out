// Filename: /models/instance.js

define([
  'underscore',
  'backbone',
  'collections/locations'
], function (_, Backbone, LocationsCollection) {
  return Backbone.Model.extend({

    idAttribute: 'uuid',

    urlRoot: '/api/instances',

    defaults: {
      active: true
    },

    initialize: function (attributes) {
      this.set(attributes);
      let locations = new LocationsCollection();
      let url = this.url();
      locations.url = function () {
        return url + '/reports'
      };
      this.set('locations', locations);
    },

    toJSON: function () {
      return _.omit(this.attributes, ['locations']);
    }

  });
});