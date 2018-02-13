// Filename: /collections/rooms.js

define([
    'backbone',
    'backbone.paginator',
    'models/room',
], function (Backbone, PageableCollection, Room) {
    return PageableCollection.extend({

        model: Room,

        url: '/api/rooms',

        parseState: function (resp, queryParams, state, options) {
            return {
                currentPage: parseInt(options.xhr.getResponseHeader('x-paginate-current-page')),
                totalPages: parseInt(options.xhr.getResponseHeader('x-paginate-total-pages')),
                totalRecords: parseInt(options.xhr.getResponseHeader('x-paginate-total-items'))
            };
        }

    });
});