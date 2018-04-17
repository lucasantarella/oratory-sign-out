// Filename: /views/students/studentslistitem.js

define([
  'underscore',
  'backbone',
  'marionette',
], function (_, Backbone, Marionette) {
  return Marionette.View.extend({

    tagName: 'tr',

    template: _.template('' +
      '   <td><%- first_name %>&nbsp;<%- last_name %></td>' +
      ''),

  });
});
