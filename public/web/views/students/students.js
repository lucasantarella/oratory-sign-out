// Filename: /views/students/students.js

define([
  'underscore',
  'backbone',
  'marionette',
  'collections/students',
  'views/students/studentslistitem'
], function (_, Backbone, Marionette, StudentsCollection, StudentListItem) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'container',

    template: _.template('' +
      '<div class="row">' +
      '<div class="s12 center-align">' +
      '<h2>Room 101</h2>' +
      '</div>' +
      '</div>' +
      '<div class="row">' +
      '<div class="s12">' +
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
      '</div>' +
      '</div>' +
      ''),

    initialize: function (options) {
      this.collection = (options.collection) ? options.collection : new StudentsCollection();
      this.collection.fetch();
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
