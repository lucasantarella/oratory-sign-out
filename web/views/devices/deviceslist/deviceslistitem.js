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
      '   <a class="btn btn-sm btm-link m-1 view-deviceslistitem-identify-button" href="#" style="color: #<%= color %>"><i class="fa fa-3x fa-<%- device_type %>"></i></a>' +
      ' </div>' +
      '</div>' +
      '<div class="flex-column">' +
      ' <div class="flex-row">' +
      '   <div class="p-1"><small>Installation ID: <%- installation_id %></small></div>' +
      '   <div class="p-1"><small>Device Token: <%- device_token %></small></div>' +
      ' </div>' +
      '</div>' +
      '</div>' +
      ''),

    ui: {
      phoneButton: '.view-deviceslistitem-identify-button'
    },

    events: {
      'click @ui.phoneButton': 'onButtonClick',
    },

    triggers: {
      'click': 'click:device'
    },

    initialize: function (options) {
      this.model = (options.model) ? options.model : new Backbone.Model();
      this.childIndex = (options.childIndex) ? options.childIndex : 0;
      this.model.on('sync', this.render); // Explicitly set to re-render when updated
    },

    serializeModel: function () {
      if (!this.model) {
        return {};
      }
      let data = _.clone(this.model.attributes);

      // Do any augmentations
      data.installation_id = (data.installation_id) ? data.installation_id.substring(0, 6) + "..." + data.installation_id.substring(30, 36) : '...';
      data.color = (data.installation_id) ? data.installation_id.substring(0, 6) : 'ffc107';
      data.device_token = (data.device_token) ? data.device_token.substring(0, 6) + "..." + data.device_token.substring(data.device_token.length - 6, data.device_token.length) : '...';
      switch (data.device_type) {
        default:
        case 'android':
          data.device_type = 'android';
          break;
        case 'ios':
          data.device_type = 'apple';
          break;
      }
      return data;
    },

    onButtonClick: function (event) {
      event.preventDefault();

      let phoneButton = this.getUI('phoneButton');

      phoneButton.addClass('fa-spin');

      // Do the Identify
      this.model.identify({
        success: function () {
          phoneButton.removeClass('fa-spin');
        }
      });
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
