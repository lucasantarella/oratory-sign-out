// Filename: /views/students/signoutmodal.js

define([
  'underscore',
  'backbone',
  'marionette',
  'collections/rooms'
], function (_, Backbone, Marionette, RoomsCollection) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'modal modal-fixed-footer',

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
      '  <div class="col s4 offset-s4">' +
      '  <a class="waves-effect waves-light btn-large blue darken-4 hidden"><i class="material-icons right">keyboard_arrow_right</i>Sign Out</a>' +
      '  </div>' +
      '  </div>' +
      '  </p>' +
      '</div>' +
      ''),

    collection: new RoomsCollection(),

    childViewContainer: 'select',

    childView: Marionette.View.extend({

      tagName: 'option',

      template: _.template('<p><%= name %></p>'),

      onRender: function () {
        this.$el.attr('value', this.model.get('name'));
      }

    }),

    loadFinished: false,

    initialize: function () {
      this.collection.fetch();
      this.collection.bind('sync', this.render)
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
    },

    open: function (context) {
      context = (context) ? context : this;
      context.instance.open();
    },

    isOpen: function (context) {
      context = (context) ? context : this;
      return context.instance.isOpen();
    },

  });
});
