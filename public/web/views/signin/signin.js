// Filename: /views/signin/signin.js

define([
  'underscore',
  'backbone',
  'marionette',
  'models/profile',
  'cookie',
  'gapi!signin2'
], function (_, Backbone, Marionette, ProfileModel, Cookies, signin2) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'container no-gutters-sm no-gutters-md',

    template: _.template('' +
      '<style>' +
      '#signInButton div {' +
      ' display:inline-block;' +
      '}' +
      '</style>' +
      '<div id="profileView"></div>' +
      '<div style=" width: 100%;max-width: 330px;padding: 15px;margin: 0 auto;" id="signInButton" class="col text-center">' +
      '</div>' +
      ''),

    regions: {
      profileInfo: '#profileView'
    },

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
      this.callback = (options.callback) ? options.callback : function () {
        console.log('no callback');
      };
    },

    onAttach: function () {
      let context = this;
      signin2.render('signInButton', {
        'width': 240,
        'height': 50,
        'longtitle': true,
        'onsuccess': function(googleUser) {
          context.callback(googleUser, context.app);
        }
      });
    },

  });
});
