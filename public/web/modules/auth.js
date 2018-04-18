//Filename: /modules/auth.js

define([
  'jquery',
  'marionette',
  'cookie',
  'views/import',
  'gapi!auth2'
], function ($, Marionette, Cookie, ImportView, auth2) {

  return Marionette.AppRouter.extend({

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
    },

    routes: {
      'logout': 'logout',
      'import': 'import',
    },

    logout: function () {
      Cookie.remove('gtoken');
      this.app.session.set('gauth', undefined);
      this.app.session.set('gtoken', undefined);
      window.OratoryUserType = null;
      auth2.getAuthInstance().signOut();
      Backbone.history.navigate('');
      location.reload();
    },

    import: function () {
      this.app.getView().showChildView('main', new ImportView());
    }

  });
});