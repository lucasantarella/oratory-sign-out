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
      '  <div class="col s12 m8 offset-m2 l6 offset-l3 center-align">' +
      '    <div class="card-panel" style="margin-top: 50px;">' +
      '      <div class="row">' +
      '        <div class="col s12">' +
      '          <img class="responsive-img" src="./img/crest.svg" width="100px"/>' +
      '          <h4>Current Room:</h4>' +
      '          <h2><%= room %></h2>' +
      '          <a class="waves-effect waves-light btn-large oratory-blue darken-4 hidden"><i class="material-icons right">keyboard_arrow_right</i>Sign Out</a>' +
      '        </div>' +
      '      </div>' +
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
        this.showChildView('modal', new SignoutModalView({
          onClose: function () {
            this.model.fetch();
          },
          callbackContext: this
        }));
    },

    onClickShowModal: function (event) {
      event.preventDefault();
      this.getChildView('modal').open();
    },

    onAttach: function () {
      $('body').addClass('oratory-blue');
    },

    onDetach: function () {
      $('body').removeClass('oratory-blue');
      this.socket.close();
    }

  });
});
