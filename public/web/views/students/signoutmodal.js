// Filename: /views/students/signoutmodal.js

define([
  'underscore',
  'backbone',
  'marionette',
], function (_, Backbone, Marionette) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'modal',

    template: _.template('' +
      '<div class="modal-content">' +
      '  <h4>Modal Header</h4>' +
      '  <p>A bunch of text</p>' +
      '</div>' +
      '<div class="modal-footer">' +
      '  <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Agree</a>' +
      '</div>' +
      ''),

    onRender: function () {
      let el = this.$el[0];
      this.instance = M.Modal.init(el, {});
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
