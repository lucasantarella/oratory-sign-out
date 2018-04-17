// Filename: /views/students/students.js

define([
  'jquery',
  'underscore',
  'backbone',
  'marionette',
  'collections/students',
  'views/students/studentslistitem'
], function ($, _, Backbone, Marionette, StudentsCollection, StudentListItem) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'container',

    template: _.template('' +
      '<div class="row">' +
      '  <div class="col s6 offset-s3">' +
      '    <div class="card" style="margin-top: 100px; padding: 20px">' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center-align">' +
      '          <h2>Room 101</h2>' +
      '        </div>' +
      '      </div>' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center">' +
      '          <table class="table">' +
      '            <thead class="thead-default">' +
      '              <tr><th>Students</th></tr>' +
      '            </thead>' +
      '            <tbody></tbody>' +
      '          </table>' +
      '        </div>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>' +
      ''),

    initialize: function (options) {
      this.collection = (options.collection) ? options.collection : new StudentsCollection();

      this.collection.fetch();
    },

    childViewContainer: 'tbody',

    childView: StudentListItem,

    onAttach: function () {
      $('body').addClass('oratory-blue');
    },

    onDetach: function () {
      $('body').removeClass('oratory-blue');
    }

  });
});
