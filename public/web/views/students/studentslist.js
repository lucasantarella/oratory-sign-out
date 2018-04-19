// Filename: /views/students/students.js

define([
  'underscore',
  'backbone',
  'marionette',
  'collections/students',
  'views/students/studentslistitem'
], function (_, Backbone, Marionette, StudentsCollection, StudentListItem) {
  return Marionette.CollectionView.extend({

    tagName: 'ul',

    className: 'collection',

    childView: StudentListItem,

    emptyView: Marionette.View.extend({

      tagName: 'li',

      className: 'collection-item',

      template: function () {
        return 'No students in this section!'
      }

    }),

    initialize: function (options) {
      this.collection = (options.collection) ? options.collection : new StudentsCollection();
      this.filters = (options.filters) ? options.filters : [];
    },

    filter: function (child, index, collection) {
      return this.filters.includes(child.get('status'));
    },

  });
});
