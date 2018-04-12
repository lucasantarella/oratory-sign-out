// Filename: /models/room.js

define([
  'backbone',
  'collections/students'
], function (Backbone, StudentsCollection) {
  return Backbone.Model.extend({

    idAttribute: 'id',

    urlRoot: '/api/profile',

    initialize: function (attributes) {

    },
  });
});