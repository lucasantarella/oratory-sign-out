// Filename: /views/rooms/rooms.js

define([
    'underscore',
    'backbone',
    'marionette',
], function (_, Backbone, Marionette) {
    return Marionette.View.extend({

        tagName: 'tr',

        template: _.template('' +
            '<th scope="row"><%- id %></th>' +
            '<td><%- first_name %></td>' +
            '<td><%- last_name %></td>' +
            '<td><%- email %></td>' +
            ''),

        onRender: function () {
            console.log(this.model);
        },

    });
});
