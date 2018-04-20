// Filename: /views/AppView.js

define([
  'underscore',
  'marionette'
], function (_, Marionette) {
  return Marionette.View.extend({

    tagName: 'header',

    template: _.template('' +
      '<ul id="slide-out" class="sidenav">' +
      '  <li><div class="user-view">' +
      '    <div class="background oratory-blue">' +
      '    </div>' +
      '    <a href="#user"><img class="circle" src="<%= guser.picture %>"></a>' +
      '    <a href="#name"><span class="white-text name"><%= guser.given_name %> <%= guser.family_name %></span></a>' +
      '    <a href="#email"><span class="white-text email"><%= guser.email %></span></a>' +
      '  </div></li>' +
      '  <li><a href="sass.html">Sass</a></li>' +
      '  <li><a href="badges.html">Components</a></li>' +
      '  <li><a href="collapsible.html">JavaScript</a></li>' +
      '</ul>' +
      '<nav class="oratory-blue darken-4 white">' +
      '<div class="nav-wrapper">' +
      '  <a href="#" class="brand-logo" style="height: 100%;"><img src="./img/logo_white.svg" width="100px" style="max-height: 100%; width: 150px;"/></a>' +
      '  <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>\n' +
      '  <ul id="nav-mobile" class="right hide-on-med-and-down">' +
      '    <li><a href="sass.html">Sass</a></li>' +
      '    <li><a href="badges.html">Components</a></li>' +
      '    <li><a href="collapsible.html">JavaScript</a></li>' +
      '  </ul>' +
      '</div>' +
      '</nav>' +
      ''),

    initialize: function (options) {
      this.app = options.app;
      this.model = options.model;
    },

    onRender: function () {
      M.Sidenav.init(this.$el.find('.sidenav'));
    },

    regions: {

      sideBarLinks: {
        el: '#slide-out',
        replaceElement: true
      },

      navBarLinks: {
        el: 'header',
        replaceElement: true
      }

    },

  });
});
