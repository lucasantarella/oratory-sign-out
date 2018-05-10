// Filename: /views/AppView.js

define([
  'underscore',
  'backbone',
  'marionette'
], function (_, Backbone, Marionette) {
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
      '  <span id="nav-list-mobile-container"></span>' +
      '</ul>' +
      '<nav class="oratory-blue darken-4 white">' +
      '<div class="nav-wrapper">' +
      '  <a href="#" class="brand-logo" style="height: 100%;"><img src="./img/logo_white.svg" width="100px" style="max-height: 100%; width: 150px;"/></a>' +
      '  <a href="#" data-target="slide-out" class="sidenav-trigger"><i class="material-icons">menu</i></a>\n' +
      '  <ul id="nav-mobile" class="right hide-on-med-and-down"></ul>' +
      '</div>' +
      '</nav>' +
      ''),

    initialize: function (options) {
      this.app = options.app;
      this.model = options.model;
      this.navLinks = new Backbone.Collection([
        {
          href: 'logout',
          title: 'Logout'
        }
      ])
    },

    onRender: function () {
      M.Sidenav.init(this.$el.find('.sidenav'));
      let childView = this.childView;

      this.showChildView('sideBarLinks', new (Marionette.CollectionView.extend({
        tagName: 'span',
        id: 'nav-list-mobile-container',
        childView: childView
      }))({collection: this.navLinks}));

      this.showChildView('navBarLinks', new (Marionette.CollectionView.extend({
        tagName: 'ul',
        id: 'nav-mobile',
        className: 'right hide-on-med-and-down',
        childView: childView
      }))({collection: this.navLinks}));
    },

    childView: Marionette.View.extend({
      tagName: 'li',
      template: _.template('<a href="#<%= href %>"><%= title %></a>'),
    }),

    regions: {

      sideBarLinks: {
        el: '#nav-list-mobile-container',
        replaceElement: true
      },

      navBarLinks: {
        el: '#nav-mobile',
        replaceElement: true
      }

    }

  });
});
