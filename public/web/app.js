// Filename: app.js

define(function (require) {

  const $ = require('jquery');
  const Backbone = require('backbone');
  const Marionette = require('marionette');
  const AppView = require('views/AppView');
  const jwt_decode = require('jwt_decode');
  const SignInView = require('views/signin/signin');
  const Cookies = require('cookie');
  const Socket = require('socket');

  // Modules
  const AuthModule = require('modules/auth');
  const RoomsModule = require('modules/rooms');
  const StudentsModule = require('modules/students');

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

    session: null,

    signedIn: false,

    setupSession: function (callback) {
      callback = (callback) ? callback : function () {

      };

      // Check if logged in...
      this.session = new Backbone.Model();

      let auth = window.localStorage.getItem('gauth');
      let token = Cookies.get('gtoken');
      if (auth !== undefined && token !== undefined) {
        try {
          token = atob(token);
          auth = JSON.parse(atob(auth));
        } catch (e) {
          // Invalid JSON...
          this.showSignIn(this);
          return;
        }

        if (Date.now() < auth.Zi.expires_at) {
          $.ajax({
            url: "https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=" + token,
            context: this,
            success: function (response) {
              if (response.error_description === undefined) {
                this.signedIn = true;
                window.OratoryUserType = (response.email.indexOf(".student") >= 1) ? "student" : "teacher";
                this.session.set('guser', response);
                this.start();
              }
            },
            complete: function () {
              this.showSignIn(this);
            }
          });
        } else
          this.showSignIn(this);
      } else
        this.showSignIn(this);
    },

    currentRoomModel: new Backbone.Model({room: ''}),

    onStart: function () {
      // Show the loading spinner
      this.showView(new AppView());

      window.socketUrl = ((location.protocol == 'https:') ? 'wss' : 'ws') + '://' + window.location.hostname + ':' + ((location.protocol == 'https:') ? '9443' : '9090');
      this.connection = new Socket({url: window.socketUrl, protocols: window.OratoryUserType});

      // Init modules
      new RoomsModule({app: this});
      new StudentsModule({app: this});
      new AuthModule({app: this});

      // Start history
      Backbone.history.start({
        // PushState: true,
        // root: '/',
      });

      Backbone.history.navigate((window.OratoryUserType === "student") ? "signout" : "students", {trigger: true});
    },

    initializeSession: function (googleUser, appContext) {
      appContext = (appContext) ? appContext : this;
      appContext.session.set('guser', jwt_decode(googleUser.getAuthResponse().id_token));
      appContext.session.set('gtoken', googleUser.getAuthResponse().id_token);
      window.localStorage.setItem('gauth', btoa(JSON.stringify(appContext.session.get('gauth'))));
      Cookies.set('gtoken', btoa(appContext.session.get('gtoken')));
      window.OratoryUserType = (googleUser.w3.U3.indexOf(".student") >= 1) ? "student" : "teacher";
      appContext.start();
    },

    showSignIn: function (context, callback) {
      context = (context) ? context : this;

      if (!context.signedIn) {
        let view = context.getView();
        if (view === undefined)
          context.showView(new SignInView({app: context, callback: context.initializeSession}));
        else
          view.showChildView('main', new SignInView({app: context, callback: context.initializeSession}));
      }

    },

  });
});