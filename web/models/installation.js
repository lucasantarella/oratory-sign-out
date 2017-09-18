// Filename: /models/installation.js

define([
  'underscore',
  'backbone',
  'collections/instances'
], function (_, Backbone, InstancesCollection) {
  return Backbone.Model.extend({

    idAttribute: 'installation_id',

    urlRoot: '/api/installations',

    defaults: {
      device_type: 'android',
      app_version: '1.0.0',
    },

    initialize: function (attributes) {
      this.set(attributes);
      let instances = new InstancesCollection();
      const url = this.url();
      instances.url = function () {
        return url + '/instances'
      };
      this.set('instances', instances);
    },

    identify: function (callback, context) {
      Backbone.$.ajax({
        type: "POST",
        context: (context) ? context : this,
        url: this.url() + "/identifications/notification",
        success: (callback.success) ? callback.success : null,
        error: (callback.error) ? callback.error : null,
      });
    },

    deprovision: function (callback, context) {
      Backbone.$.ajax({
        type: "DELETE",
        context: (context) ? context : this,
        url: this.url() + "/instances",
        success: (callback.success) ? callback.success : null,
        error: (callback.error) ? callback.error : null,
      });
    },

    requestLocationReport: function (callback, context) {
      Backbone.$.ajax({
        type: "POST",
        context: (context) ? context : this,
        url: this.url() + "/requests/location/report",
        success: (callback.success) ? callback.success : null,
        error: (callback.error) ? callback.error : null,
      });
    },

    toJSON: function (options) {
      return _.omit(this.attributes, ['instances']);
    },

  });
});