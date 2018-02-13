//Filename: /modules/rooms.js

define([
    'marionette',
    'views/rooms/rooms',
    'views/rooms/roomstudents/students',
    'models/room',
    'collections/rooms',
], function (Marionette, RoomsView, StudentsView, RoomModel, RoomsCollection) {

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
            'rooms/:room/students': 'students',
        },

        listRooms: function (room) {
            let view = new RoomsView({collection: this.rooms});
            this.app.getView().showChildView('main', view);
            if (room) {
                let model = new RoomModel({name: room});
                this.rooms.add(model);
                model.fetch();
                view.showPanelInfo(model);
            }
        },

        students: function (room) {
            let model = new RoomModel({name: room});
            model.fetch();
            this.rooms.add(model);
            let view = new StudentsView({collection: model.get('students'), room: model});
            this.app.getView().showChildView('main', view);
        },

    });
});