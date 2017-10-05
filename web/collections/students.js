// Filename: /collections/students.js

define([
    'backbone',
    'backbone.paginator',
    'models/student',
], function (Backbone, PageableCollection, Student) {
    return Backbone.Collection.extend({

        model: Student,

        url: '/api/students',

        // parseState: function (resp, queryParams, state, options) {
        //     return {
        //         currentPage: parseInt(options.xhr.getResponseHeader('x-paginate-current-page')),
        //         totalPages: parseInt(options.xhr.getResponseHeader('x-paginate-total-pages')),
        //         totalRecords: parseInt(options.xhr.getResponseHeader('x-paginate-total-items'))
        //     };
        // }

    });
});