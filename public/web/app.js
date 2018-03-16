// Filename: app.js

define(function (require) {

  const Backbone = require('backbone');
  const Marionette = require('marionette');
  const AppView = require('views/AppView');
  const Cookies = require('cookie');
  const auth2 = require('gapi!signin2');

  // Modules
  const RoomsModule = require('modules/rooms');
  const StudentsModule = require('modules/students');
  const SignInModule = require('modules/signin');

  return Marionette.Application.extend({

    // Provide a helper function for navigating within modules
    navigate: function (route, options) {
      options || (options = {});
      Backbone.history.navigate(route, options);
    },

    // Helper method for getting the current route
    getCurrentRoute: function () {
      return Backbone.history.fragment;
    },

    // Define the element where the application will exist
    region: 'main',

    session: null,

    onStart: function () {

      // Init modules
      new RoomsModule({app: this});
      new StudentsModule({app: this});
      new SignInModule({app: this});

      // Show the root view
      this.showView(new AppView());

      // Start history
      Backbone.history.start({
        // PushState: true,
        // root: '/',
      });

      // Check if logged in...
      let auth = window.localStorage.getItem('gauth');
      let token = Cookies.get('gtoken');
      if (auth == undefined || token == undefined) {
        Backbone.history.navigate('signin', {trigger: true});
      }

      // Init user session
      this.session = new Backbone.Model();

      token = atob(token);
      auth = JSON.parse(atob(auth));
      if (Date.now() >= auth.Zi.expires_at) {
        Backbone.history.navigate('signin', {trigger: true});
      }

      this.initializeSession(auth, token, this);
    },

    initializeSession: function (gauth, gtoken, appContext) {
      let context = (appContext) ? appContext : this;
      context.session.set('gauth', gauth);
      context.session.set('gtoken', gtoken);
      return context.session;
    },

  });
});
