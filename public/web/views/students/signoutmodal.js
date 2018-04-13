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
      '  <h4>Signout To</h4>' +
      '  <p>' +
      '  <div class="row">' +
      '  <div class="collection col s4 offset-s4" style="padding-left: 0; padding-right: 0;">' +
      '  </div>' +
      '  </div>' +
      '  </p>' +
      '</div>' +
      '<div class="modal-footer">' +
      '  <a class="modal-action modal-close waves-effect waves-green btn-flat">Agree</a>' +
      '</div>' +
      ''),

    collection: new RoomsCollection(),

    childViewContainer: '.collection',

    childView: Marionette.View.extend({

      tagName: 'div',

      className: 'collection-item',

      template: _.template('<p><%= name %></p>'),

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
