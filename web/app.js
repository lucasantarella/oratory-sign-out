// Filename: app.js

define(function (require) {

  const Backbone = require('backbone');
  const Marionette = require('marionette');
  const AppView = require('views/AppView');

  // Modules
  const DevicesModule = require('modules/devices');

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
    region: 'body',

    onStart: function () {
      // Init modules
      new DevicesModule({app: this});

      // Show the root view
      this.showView(new AppView());

      // Start history
      Backbone.history.start({
        // PushState: true,
        // root: '/',
      });
    }

  });
});
