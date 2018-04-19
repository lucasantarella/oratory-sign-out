// Filename: /views/students/studentslistitem.js

define([
  'underscore',
  'backbone',
  'marionette',
], function (_, Backbone, Marionette) {
  return Marionette.View.extend({

    tagName: 'li',

    className: 'collection-item left-align',

    template: _.template('<%- first_name %>&nbsp;<%- last_name %></td>'),

    onRender: function() {
      switch (this.model.get('status')){
        case 'signedin_unconfirmed':
          this.$el.addClass('orange');
          this.$el.addClass('white-text');
          break;
        case 'signedin_confirmed':
          this.$el.addClass('green');
          this.$el.addClass('white-text');
          break;
        case 'signedout':
          this.$el.addClass('red');
          this.$el.addClass('white-text');
          break;
      }
    }

  });
});
