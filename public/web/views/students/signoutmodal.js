// Filename: /views/students/signoutmodal.js

define([
  'jquery',
  'underscore',
  'backbone',
  'marionette',
  'collections/rooms',
], function ($, _, Backbone, Marionette, RoomsCollection) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'modal modal-fixed-footer',

    attributes: {
      style: 'height:50%;'
    },

    template: _.template('' +
      '<div class="modal-content">' +
      '  <h4 style="text-align:center;">Signout To</h4>' +
      '  <p>' +
      '  <div class="row" style="margin-top: 60px">' +
      '  <div class="col s6 offset-s3">' +
      '  <select class="browser-default" style="font-size:18px;"></select>' +
      '  </div>' +
      '  </div>' +
      '  <div class="row" style="margin-top: 60px">' +
      '  <div class="col s10 offset-s1 m6 offset-m3 l4 offset-l4 center-align">' +
      '  <a class="waves-effect waves-light btn-large oratory-blue darken-4 hidden"><i class="material-icons right">keyboard_arrow_right</i>Sign Out</a>' +
      '  </div>' +
      '  </div>' +
      '  </p>' +
      '</div>' +
      ''),

    ui: {
      'signout': 'a',
      'roomSelect': 'select'
    },

    events: {
      'click @ui.signout': 'onClickSignOut',
    },

    collection: new RoomsCollection(),

    childViewContainer: 'select',

    childView: Marionette.View.extend({

      tagName: 'option',

      template: _.template('<p><%= room %></p>'),

      serializeModel: function serializeModel() {
        if (!this.model) {
          return {};
        }
        let data = _.clone(this.model.attributes);
        data.room = data.name.replace('-', ' ');
        if (parseInt(data.name) > 0)
          data.room = 'Room ' + data.room;
        return data;
      },

      onRender: function () {
        this.$el.attr('value', this.model.get('name'));
      }

    }),

    loadFinished: false,

    initialize: function (options) {
      this.collection.fetch();
      this.collection.bind('sync', this.render);
      this.onClose = (options.onClose) ? options.onClose : function () {};
      this.callbackContext = (options.callbackContext) ? options.callbackContext : this;
    },

    onRender: function () {
      if (this.instance === undefined)
        this.instance = M.Modal.init(this.$el[0]);
    },

    onAttach: function () {
      this.select = M.FormSelect.init(this.$el.find('select'));
    },

    close: function (context) {
      context = (context) ? context : this;
      context.instance.close();
      this.onClose.call(this.callbackContext);
    },

    open: function (context) {
      context = (context) ? context : this;
      context.instance.open();
    },

    isOpen: function (context) {
      context = (context) ? context : this;
      return context.instance.isOpen();
    },

    onClickSignOut: function (event) {
      event.preventDefault();
      $.ajax({
        type: "POST",
        context: this,
        url: "/api/students/me/logs",
        data: JSON.stringify({room_to: this.getUI('roomSelect').val()}),
        contentType: "application/json; charset=utf-8",
        success: function () {
          this.close();
        },
        error: function () {
          this.close();
          alert('Error signing out!');
        }
      });
    }

  });
});
