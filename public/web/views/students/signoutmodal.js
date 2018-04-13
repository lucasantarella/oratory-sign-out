// Filename: /views/students/signoutmodal.js

define([
  'underscore',
  'backbone',
  'marionette',
  'collections/rooms'
], function (_, Backbone, Marionette, RoomsCollection) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'modal',

    template: _.template('' +
      '<div class="modal-content">' +
      '  <h4>Signout To</h4>' +
      '  <div class="row">' +
      '  <div class="input-field col s4 offset-s4">' +
      '  <label>Room Selection</label>' +
      '  <select>' +
      '  </select>' +
      '  </div>' +
      '  </div>' +
      '</div>' +
      '<div class="modal-footer">' +
      '  <a class="modal-action modal-close waves-effect waves-green btn-flat">Agree</a>' +
      '</div>' +
      ''),

    ui: {
      'select': 'select'
    },

    collection: new RoomsCollection(),

    childViewContainer: 'select',

    childView: Marionette.View.extend({

      el: 'option',

      template: _.template('<%= name %>'),

      onRender: function () {
        this.$el.attr('value', this.model.get('name'))
      }

    }),

    loadFinished: false,

    initialize: function () {
      this.collection.fetch();

      let context = this;
      this.collection.bind('sync', function () {
        context.loadFinished = true;
        context.render();
      })
    },

    onRender: function () {
      if (this.instance === undefined)
        this.instance = M.Modal.init(this.$el[0]);
    },

    onDomRefresh: function () {
      if(this.select !== undefined)
        this.select.destroy();
      if (this.loadFinished)
        this.select = M.FormSelect.init(this.getUI('select')[0]);
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
