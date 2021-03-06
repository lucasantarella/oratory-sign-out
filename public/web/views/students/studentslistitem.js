// Filename: /views/students/studentslistitem.js

define([
  'underscore',
  'backbone',
  'marionette',
  'models/log'
], function (_, Backbone, Marionette, LogModel) {
  return Marionette.View.extend({

    tagName: 'li',

    className: 'collection-item left-align valign-wrapper',

    attributes: {
      'style': 'padding: 0.8rem 2rem 0.8rem 2rem;'
    },

    template: _.template('' +
      '<span class="truncate"><%- last_name %>, <%- first_name %></span>' +
      '&nbsp;' +
      '<% if(status === \'signedout_unconfirmed\' || status === \'signedout_confirmed\') { %>' +
      '  <span class="badge white red-text right">' +
      '    <% if(parseInt(signedout_room) > 0) { %><span class="hide-on-small-and-down">Room </span><% } %>' +
      '    <span>' +
      '      <%= signedout_room %>' +
      '      <% if(status === \'signedout_confirmed\') { %><span class="green-text">&check;</span><% } else { %>&times;<% } %>' +
      '    </span>' +
      '  </span>' +
      '<% } %>' +
      '<% if(status === \'signedin_unconfirmed\') { %>' +
      '  <span class="badge green white-text right">' +
      '    <a href="#" class="white-text">Accept</a>' +
      '  </span>' +
      '<% } %>' +
      ''),

    serializeModel: function serializeModel() {
      if (!this.model) {
        return {};
      }
      let data = _.clone(this.model.attributes);
      if (data.signedout_room !== undefined && data.signedout_room !== null && data.signedout_room.length > 0)
        data.signedout_room = data.signedout_room.replace('-', ' ');
      else
        data.signedout_room = '';
      return data;
    },

    ui: {
      accept: 'a'
    },

    events: {
      'click @ui.accept': 'onClickAccept'
    },

    initialize: function (options) {
      this.model = options.model;
      this.model.bind('sync', this.render);
    },

    onRender: function () {
      this.$el.removeClass();
      this.$el.addClass(this.className);
      switch (this.model.get('status')) {
        case 'scheduled':
          this.$el.addClass('grey');
          this.$el.addClass('darken-4');
          this.$el.addClass('white-text');
          break;
        case 'signedin_unconfirmed':
          this.$el.addClass('orange');
          this.$el.addClass('white-text');
          break;
        case 'signedin_confirmed':
          this.$el.addClass('green');
          this.$el.addClass('white-text');
          break;
        case 'signedout_unconfirmed':
        case 'signedout_confirmed':
          this.$el.addClass('red');
          this.$el.addClass('white-text');
          break;
      }
    },

    onClickAccept: function (event) {
      event.preventDefault();
      let model = this.model;
      let logModel = new LogModel({id: parseInt(this.model.get('signout_id')), confirmed: true});
      logModel.url = '/api/students/' + parseInt(this.model.get('id')) + '/logs/' + parseInt(logModel.get('id'));
      logModel.save();
    }

  });
});
