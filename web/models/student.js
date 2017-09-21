// Filename: /models/student.js

define([
    'backbone'
], function (Backbone) {
    return Backbone.Model.extend({

        defaults: {
            first_name: '',
            middle_name: '',
            last_name: '',
            email: '',
        }

    });
});