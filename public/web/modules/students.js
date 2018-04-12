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
      this.students = new StudentsCollection();
      // this.students.getFirstPage();
    },

    routes: {
      'students': 'listStudents',
      'signout': 'studentSignOut',
      // 'students/:student',
    },

    listStudents: function (student) {
      let view = new StudentsView({collection: this.students});
      this.app.getView().showChildView('main', view);
    },

    studentSignOut: function () {
      let view = new SignoutView({collection: this.students});
      this.app.getView().showChildView('main', view);
    },

  });
});