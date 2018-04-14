// Filename: /views/rooms/roomstudents/roompickermodal.js

define([
  'underscore',
  'backbone',
  'marionette',
  'collections/rooms',
  'models/room'
], function (_, Backbone, Marionette, RoomsCollection, RoomModel) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'modal fade',

    attributes: {
      // 'tabindex': '-1',
      'role': 'dialog',
      'aria-labelledby': 'Room Picker',
      'aria-hidden': 'true'
    },

    template: _.template('' +
      '<div class="modal-dialog" role="document">' +
      '    <div class="modal-content">' +
      '      <div class="modal-header">' +
      '        <h5 class="modal-title" id="exampleModalLabel">Select a Room to Sign Out to</h5>' +
      '        <button type="button" class="close" aria-label="Close">' +
      '          <span aria-hidden="true">&times;</span>' +
      '        </button>' +
      '      </div>' +
      '      <div class="modal-body">' +
      '           <div class="form">' +
      '               <select>' +
      '               </select>' +
      '           </div>' +
      '      </div>' +
      '      <div class="modal-footer">' +
      '        <button type="button" class="btn btn-warning modal-btn-submit">Sign Out</button>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      ''),

    ui: {
      submitButton: '.btn',
      roomSelect: 'select'
    },

    triggers: {
      'click @ui.submitButton': 'submit'
    },

    initialize: function (options) {
      this.collection = new (Backbone.Collection.extend({
        model: RoomModel,
        url: '/api/rooms'
      }))();

      this.collection.fetch();
      this.collection.on('sync', this.render);
    },

    onSubmit: function () {
      let val = this.getUI('roomSelect').val();
      console.log('onClick: ' + val);
      if (this.submitCallback)
        this.submitCallback.call(this.callbackContext, val);
      this.hideModal();
    },

    showModal: function (callback, context) {
      this.$el.modal('show');

      if (callback && callback.submit)
        this.submitCallback = callback.submit;

      if (callback && context)
        this.callbackContext = context;
      else
        this.callbackContext = this;

      let self = this;
      let onSubmit = this.onSubmit;
      this.getUI('submitButton').on('click', function () {
        onSubmit.call(self);
      });
    },

    hideModal: function () {
      this.$el.modal('hide');
    },

    childViewContainer: 'select',

    childView: Marionette.View.extend({

      tagName: 'option',

      template: _.template('<%- name %>'),

      onRender: function () {
        this.$el.attr('value', this.model.get('name'));
      }
    }),

  });
});
