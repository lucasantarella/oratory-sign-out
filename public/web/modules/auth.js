//Filename: /modules/auth.js

define([
  'jquery',
  'marionette',
  'cookie'
], function ($, Marionette, Cookie) {

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
      Backbone.history.navigate('');
      location.reload();
    },

    import: function() {
      var formData = new FormData();
      formData.append('section', 'general');
      formData.append('action', 'previewImg');
// Attach file
      formData.append('image', $('input[type=file]')[0].files[0]);
      $.ajax({
        url: '/api/import',
        data: formData,
        type: 'POST',
        contentType: false,
        processData: false,
        success: function(response) {
          console.log(response);
        }
      });
    }

  });
});