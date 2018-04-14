//Filename: /modules/auth.js

define([
  'marionette',
  'cookie'
], function (Marionette, Cookie) {

  return Marionette.AppRouter.extend({

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
    },

    routes: {
      'logout': 'logout',
    },

    logout: function () {
      Cookie.remove('gtoken');
      this.app.session.set('gauth', undefined);
      this.app.session.set('gtoken', undefined);
      window.OratoryUserType = null;
      Backbone.history.navigate('');
      location.reload();
    },

  });
});