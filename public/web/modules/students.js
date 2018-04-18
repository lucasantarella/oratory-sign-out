//Filename: /modules/students.js

define([
  'backbone',
  'marionette',
  'views/students/students',
  'views/students/signout',
  'models/student',
  'collections/students',
], function (Backbone, Marionette, StudentsView, SignoutView, StudentModel, StudentsCollection) {

  return Marionette.AppRouter.extend({

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
      this.currentRoomModel = (options.currentRoomModel) ? options.currentRoomModel : (app.currentRoomModel) ? app.currentRoomModel : new Backbone.Model({room: ''});
      this.students = new StudentsCollection();
      // this.students.getFirstPage();
    },

    routes: {
      'students': 'listStudents',
      'signout': 'studentSignOut',
      // 'students/:student',
    },

    listStudents: function (student) {
      let view = new StudentsView({collection: this.students, model: this.currentRoomModel});
      this.app.getView().showChildView('main', view);
    },

    studentSignOut: function () {
      let view = new SignoutView({collection: this.students});
      this.app.getView().showChildView('main', view);
    },

  });
});