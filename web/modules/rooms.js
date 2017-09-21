//Filename: /modules/rooms.js

define([
    'marionette',
    'views/rooms/rooms',
    'models/room',
    'collections/rooms',
], function (Marionette, RoomsView, RoomModel, RoomsCollection) {

    return Marionette.AppRouter.extend({

        initialize: function (options) {
            this.app = (options.app) ? options.app : null;
            this.rooms = new RoomsCollection();
            this.rooms.getFirstPage();
        },

        routes: {
            '': 'listRooms',
            'rooms': 'listRooms',
            'rooms/:room': 'listRooms',
            // 'rooms/:room/students/:instance_id': 'viewInstance',
        },

        listRooms: function (room) {
            let view = new RoomsView({collection: this.rooms});
            this.app.getView().showChildView('main', view);
        },

    });
});