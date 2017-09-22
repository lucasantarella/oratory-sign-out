//Filename: /modules/students.js

define([
    'marionette',
    'views/students/students',
    'models/student',
    'collections/students',
], function (Marionette, StudentsView, StudentModel, StudentsCollection) {

    return Marionette.AppRouter.extend({

        initialize: function (options) {
            this.app = (options.app) ? options.app : null;
            this.students = new StudentsCollection();
            // this.students.getFirstPage();
        },

        routes: {
            'students': 'listStudents',
            // 'students/:student',
        },

        listStudents: function (student) {
            let view = new StudentsView({collection: this.students});
            this.app.getView().showChildView('main', view);
        },

    });
});