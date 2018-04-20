//Filename: /modules/students.js

define([
  'backbone',
  'marionette',
  'views/NavBar',
  'views/students/students',
  'views/students/signout',
  'models/student',
  'collections/students',
], function (Backbone, Marionette, NavBarView, StudentsView, SignoutView, StudentModel, StudentsCollection) {

  return Marionette.AppRouter.extend({

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
      this.currentRoomModel = (options.currentRoomModel) ? options.currentRoomModel : (this.app.currentRoomModel) ? this.app.currentRoomModel : new Backbone.Model({room: ''});
      this.students = new StudentsCollection();
      // this.students.getFirstPage();
    },

    routes: {
      'students': 'listStudents',
      'signout': 'studentSignOut'
    },

    listStudents: function () {
      let view = new StudentsView({collection: this.students, model: this.currentRoomModel});
      this.app.getView().showChildView('main', view);
      this.app.getView().showChildView('header', new NavBarView({app: this.app, model: this.app.session}));
    },

    studentSignOut: function () {
      let view = new SignoutView({collection: this.students});
      this.app.getView().showChildView('main', view);
      this.app.getView().showChildView('header', new NavBarView({app: this.app, model: this.app.session}));
    },

  });
});