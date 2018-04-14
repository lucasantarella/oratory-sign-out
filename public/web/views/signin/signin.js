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

    className: 'container',

    template: _.template('' +
      '<style>' +
      'body {' +
      ' background-color: #00235a;' +
      '}' +
      '#signInButton div  {' +
      'margin: 0 auto;' +
      '}' +
      '</style>' +
      '<div class="row">' +
      '  <div class="col s6 offset-s3">' +
      '    <img class="responsive-img" src="./img/logo.png">' +
      '  </div>' +
      '</div>' +
      '<div class="row">' +
      '  <div class="col s6 offset-s3 center-align" id="wrapper">' +
      '    <div id="signInButton">text</div>' +
      '  </div> ' +
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
        'onsuccess': function (googleUser) {
          context.callback(googleUser, context.app);
        }
      });
    },

  });
});
