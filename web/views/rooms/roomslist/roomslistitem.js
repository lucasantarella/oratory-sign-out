// Filename: /views/devices/deviceslist/deviceslistitem.js

define([
  'underscore',
  'backbone',
  'marionette'
], function (_, Backbone, Marionette) {
  return Marionette.View.extend({

    tagName: 'a',

    className: 'list-group-item list-group-item-action',

    attributes: {
      href: '#'
    },

    template: _.template('' +
      '<div class="d-flex">' +
      '<div class="flex-column">' +
      ' <div class="flex-row">' +
      '   <div class="p-1"><small><%- name %></small></div>' +
      ' </div>' +
      '</div>' +
      '</div>' +
      ''),

    triggers: {
      'click': 'click:room'
    },

    initialize: function (options) {
      this.model = (options.model) ? options.model : new Backbone.Model();
      this.childIndex = (options.childIndex) ? options.childIndex : 0;
      this.model.on('sync', this.render); // Explicitly set to re-render when updated
    },

    setActive: function () {
      this.$el.addClass('active');
      this.$el.addClass('text-white');
    },

    setInactive: function () {
      this.$el.removeClass('active');
      this.$el.removeClass('text-white');
    }

  });
});
