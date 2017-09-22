// Filename: /models/room.js

define([
    'backbone',
    'collections/students'
], function (Backbone, StudentsCollection) {
    return Backbone.Model.extend({

        idAttribute: 'name',

        urlRoot: '/api/rooms',

        initialize: function (attributes) {
            this.set(attributes);
            let students = new StudentsCollection();
            const url = this.url();
            students.url = function () {
                return url + '/students'
            };
            this.set('students', students);
        },
    });
});