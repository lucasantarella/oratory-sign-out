// Filename: /views/students/students.js

define([
  'underscore',
  'backbone',
  'marionette',
  'models/period',
], function (_, Backbone, Marionette, PeriodModel) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'container',

    template: _.template('' +
      '<div class="row">' +
      '  <div class="col s4 offset-s4" style="text-align: center">' +
      '    <h4>Current Room:</h4>' +
      '    <h2><%= room %></h2>' +
      '    <a class="waves-effect waves-light btn-large blue darken-4"><i class="material-icons right">keyboard_arrow_right</i>Sign Out</a>' +
      '  </div>' +
      '</div>' +
      ''),

    initialize: function (options) {
      this.model = new PeriodModel();
      this.model.fetch();
      this.model.bind('sync', this.render);
    },

  });
});
