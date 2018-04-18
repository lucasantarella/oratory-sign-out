// Filename: /views/import.js

define([
  'underscore',
  'backbone',
  'marionette',
], function (_, Backbone, Marionette) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'container',

    template: _.template('' +
      '<form method="POST" action="/api/import/students" enctype="multipart/form-data">' +
      '<input type="file" name="schedules" />' +
      '<input type="submit" value="Submit">' +
      '</form>' +
      ''),

  });
});
