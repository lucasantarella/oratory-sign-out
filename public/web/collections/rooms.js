// Filename: /collections/rooms.js

define([
  'underscore',
  'backbone',
  'models/room'
], function (_, Backbone, Room) {
  return Backbone.Collection.extend({

    model: Room,

    url: '/api/rooms',

  });
});