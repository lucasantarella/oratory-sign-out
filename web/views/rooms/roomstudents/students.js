// Filename: /views/rooms/roomstudents/students.js

define([
    'underscore',
    'backbone',
    'marionette',
    'collections/students',
    'views/rooms/roomstudents/studentslist',
    'views/rooms/roomstudents/roompickermodal'
], function (_, Backbone, Marionette, StudentsCollection, StudentList, RoomsModalView) {
    return Marionette.View.extend({

        tagName: 'div',

        className: 'container no-gutters-sm no-gutters-md',

        template: _.template('' +
            '<div class="students-list"></div>' +
            '<div class="modal-holder"></div>' +
            ''),

        regions: {
            modal: {
                el: '.modal-holder',
                replaceElement: true
            },
            list: {
                el: '.students-list',
                replaceElement: true
            },
        },

        initialize: function (options) {
            this.room = options.room;
            this.collection = (options.collection) ? options.collection : new StudentsCollection();
            if (!options.collection)
                this.collection.fetch();
        },

        onRender: function () {
            this.showChildView('list', new StudentList({room: this.room, collection: this.collection}));
            this.showChildView('modal', new RoomsModalView({collection: this.collection}));
        },

        childViewEvents: {
            'child:click:sign:out': 'onChildviewClickSignOut'
        },

        onChildviewClickSignOut: function (childView) {
            return this.getChildView('modal').showModal({
                submit: function (value) {
                    childView.model.signOut(this.room.get('name'), value, {
                        success: function () {
                            this.collection.fetch();
                        }
                    }, this);
                }
            }, this);

        }

    });
});
