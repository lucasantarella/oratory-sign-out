//Filename: /modules/signin.js

define([
  'marionette',
  'views/signin/signin',
], function (Marionette, SignInView) {

  return Marionette.AppRouter.extend({

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
    },

    routes: {
      'signin': 'signin',
    },

    signin: function () {
      let view = new SignInView();
      this.app.getView().showChildView('main', view);
    },

  });
});