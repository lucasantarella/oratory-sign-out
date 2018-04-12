// Filename: /views/rooms/roomstudents/studentslistitem.js

define([
  'underscore',
  'backbone',
  'marionette',
], function (_, Backbone, Marionette) {
  return Marionette.View.extend({

    tagName: 'tr',

    template: _.template('' +
      '   <th scope="row"><%- id %></th>' +
      '   <td><%- first_name %></td>' +
      '   <td><%- last_name %></td>' +
      '   <td><%- email %></td>' +
      '   <td><button class="btn btn-danger btn-sign-out hidden-xs-up">Sign Out</button><button class="btn btn-success btn-confirm-sign-in hidden-xs-up">Sign In</button></td>' +
      ''),

    ui: {
      signOutButton: '.btn-sign-out',
      confirmSigninButton: '.btn-confirm-sign-in',
    },

    triggers: {
      'click @ui.signOutButton': 'click:sign:out'
    },

    onRender: function () {
      switch (this.model.get('status')) {
        case 'signedin_unconfirmed':
          this._setUnconfirmedSignin();
          break;
        case 'signedout':
          this._setSignedOut();
          break;
        case 'scheduled':
        case 'signedin_confirmed':
          this._setConfirmedSignin();
          break;
      }
    },

    _setUnconfirmedSignin: function () {
      this.$el.addClass('bg-warning');
      this.getUI('confirmSigninButton').removeClass('hidden-xs-up');
    },

    _setConfirmedSignin: function () {
      this.getUI('signOutButton').removeClass('hidden-xs-up');
    },

    _setSignedOut: function () {
      this.$el.addClass('bg-danger').addClass('text-white');
    },

  });
});
