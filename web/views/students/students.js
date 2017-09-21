// Filename: /views/rooms/rooms.js

define([
    'underscore',
    'backbone',
    'marionette',
    'collections/students',
    'views/students/studentslistitem'
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
            '    </tr>' +
            '  </thead>' +
            '  <tbody>' +
            '  </tbody>' +
            '</table>' +
            ''),

        initialize: function (options) {
            this.collection = (options.collection) ? options.collection : new StudentsCollection();
        },

        childViewContainer: 'tbody',

        childView: StudentListItem,

        childViewEvents: {
            // 'child:click:room': 'onRoomSelected',
            //'child:click:instance': 'onInstanceSelected'
        },

        _setChildSelected: function (model) {
            let roomsView = this.getChildView('roomsList');
            roomsView.children.each(function (e) {
                e.setInactive();
            });
            roomsView.children.findByModel(roomsView.collection.findWhere({installation_id: model.get('installation_id')})).setActive();
        }

    });
});
