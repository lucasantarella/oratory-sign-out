// Filename: /views/students/studentslistitem.js

define([
    'underscore',
    'backbone',
    'marionette',
], function (_, Backbone, Marionette) {
    return Marionette.View.extend({

        tagName: 'tr',

        template: _.template('' +
            '   <th scope="row"><%- id %></th>' +
            '   <td><%- first_name %></td>' +
            '   <td><%- last_name %></td>' +
            '   <td><%- email %></td>' +
            ''),

    });
});
