// Filename: /models/student.js

define([
    'jquery',
    'backbone'
], function ($, Backbone) {
    return Backbone.Model.extend({

        urlRoot: '/api/students',

        defaults: {
            first_name: '',
            middle_name: '',
            last_name: '',
            email: ''
        },
        
        signOut: function (roomFrom, roomTo, callback, context) {
            $.ajax({
                type: "POST",
                context: (context) ? context : this,
                url: this.url() + "/logs",
                data: JSON.stringify({room_from: roomFrom, room_to: roomTo}),
                success: (callback.success) ? callback.success : null,
                error: (callback.error) ? callback.error : null,
            });
        }

    });
});