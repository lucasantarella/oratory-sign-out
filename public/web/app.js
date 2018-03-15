// Filename: app.js

define(function (require) {

  const Backbone = require('backbone');
  const Marionette = require('marionette');
  const AppView = require('views/AppView');

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
      if (auth !== undefined) {

        Backbone.history.navigate('signin', {trigger: true});
      }
    }

  });
});
