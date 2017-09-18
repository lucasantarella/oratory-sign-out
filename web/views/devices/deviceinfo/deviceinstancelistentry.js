// Filename: /views/devices/deviceinfo/deviceinstancelistentry.js

define([
  'underscore',
  'backbone',
  'marionette'
], function (_, Backbone, Marionette) {
  return Marionette.View.extend({

    tagName: 'a',

    attributes: {
      href: '#'
    },

    className: 'list-group-item list-group-item-action d-flex align-items-center btn',

    template: _.template('<span class="truncate-xs">UUID: <%- uuid %></span><span class="btn ml-auto badge badge-<%= active_class %>"><%- active %></span>'),

    ui: {
      badge: 'a'
    },

    triggers: {
      'click': 'click:instance'
    },

    serializeModel: function () {
      if (!this.model) {
        return {};
      }
      var data = _.clone(this.model.attributes);

      // Do any augmentations
      data.uuid = (data.uuid) ? data.uuid : 'Loading...';
      data.active_class = (data.active) ? 'success' : 'danger';
      data.active = (data.active) ? 'Active' : 'Inactive';
      return data;
    },

    onRender: function () {
      if (!this.model.get('active')) {
        this.$el.addClass('disabled');
        this.$el.prop('disabled', true);
        this.getUI('badge').addClass('disabled');
      }
    }

  });
});
