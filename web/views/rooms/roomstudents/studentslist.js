// Filename: /views/rooms/roomstudents/students.js

define([
    'underscore',
    'backbone',
    'marionette',
    'collections/students',
    'views/rooms/roomstudents/studentslistitem',
], function (_, Backbone, Marionette, StudentsCollection, StudentListItem) {
    return Marionette.CompositeView.extend({

        tagName: 'div',

        className: 'container no-gutters-sm no-gutters-md',

        template: _.template('' +
            '<table class="table">' +
            '  <thead class="thead-default">' +
            '    <tr>' +
            '      <th>#</th>' +
            '      <th>First Name</th>' +
            '      <th>Last Name</th>' +
            '      <th>Email</th>' +
            '      <th></th>' +
            '    </tr>' +
            '  </thead>' +
            '  <tbody>' +
            '  </tbody>' +
            '</table>' +
            '<div class="modal-holder"></div>' +
            ''),

        regions: {
            modal: '.modal-holder'
        },

        initialize: function (options) {
            this.room = options.room;
            this.collection = (options.collection) ? options.collection : new StudentsCollection();
            this.collection.fetch();
            this.collection.on('sync', this.render);
        },

        childViewContainer: 'tbody',

        childView: StudentListItem,

        childViewTriggers: {
            'click:sign:out': 'child:click:sign:out'
        },

        _setChildSelected: function (model) {
            let roomsView = this.getChildView('roomsList');
            roomsView.children.each(function (e) {
                e.setInactive();
            });
            roomsView.children.findByModel(roomsView.collection.findWhere({installation_id: model.get('installation_id')})).setActive();
        },

    });
});
