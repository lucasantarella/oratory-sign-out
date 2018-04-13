// Filename: /views/students/signout.js

define([
  'underscore',
  'backbone',
  'marionette',
  'models/period',
  'views/students/signoutmodal',
], function (_, Backbone, Marionette, PeriodModel, SignoutModalView) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'container',

    template: _.template('' +
      '<div class="row">' +
      '  <div class="col s4 offset-s4" style="text-align: center">' +
      '    <h4>Current Room:</h4>' +
      '    <h2><%= room %></h2>' +
      '    <a class="waves-effect waves-light btn-large blue darken-4 hidden"><i class="material-icons right">keyboard_arrow_right</i>Sign Out</a>' +
      '  </div>' +
      '</div>' +
      '<div id="signout-modal"></div>' +
      ''),

    ui: {
      'button': '.btn-large'
    },

    events: {
      'click @ui.button': 'onClickShowModal'
    },

    regions: {
      'modal': {
        'el': '#signout-modal',
        replaceElement: true
      }
    },

    initialize: function (options) {
      this.model = new PeriodModel();
      this.model.fetch();
      this.model.bind('sync', this.render);
    },

    onRender: function () {
      if (this.model.get('room').length === 0)
        this.getUI('button').hide();
      else
        this.showChildView('modal', new SignoutModalView());
    },

    onClickShowModal: function (event) {
      event.preventDefault();
      this.getChildView('modal').open();
    }

  });
});
