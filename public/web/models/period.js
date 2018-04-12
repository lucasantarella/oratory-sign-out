// Filename: /models/room.js

define([
  'underscore',
  'backbone',
], function (_, Backbone) {
  return Backbone.Model.extend({

    idAttribute: 'period',

    defaults: {
      'period': 0,
      'start_time': '',
      'end_time': '',
      'room': ''
    },

    url: '/api/schedules/now',


  });
});