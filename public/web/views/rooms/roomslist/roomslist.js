// Filename: /views/rooms/roomslist/roomslist.js

define([
  'backbone',
  'marionette',
  'views/rooms/roomslist/roomslistitem',
  'css!styles/views/roomslist/roomslist.css'
], function (Backbone, Marionette, RoomsListItem) {
  return Marionette.CollectionView.extend({

    tagName: 'div',

    className: 'list-group p-2',

    attributes: {
      style: 'padding-top: 0; padding-bottom: 0;'
    },

    childView: RoomsListItem,

    childViewOptions: function (model, index) {
      return {
        model: model,
        childIndex: index
      }
    },

    childViewTriggers: {
      'click:room': 'child:click:room'
    },

    initialize: function (options) {
      this.pageableCollection = options.collection;
      this.collection = new Backbone.Collection([]);


      let collection = this.collection;
      let pageableCollection = this.pageableCollection;
      this.pageableCollection.bind('sync', function () {
        collection.add(pageableCollection.toJSON());
      });
      this.pageableCollection.bind('sync', this.render);
    },

    onRender: function () {
      if (this.$el.height() <= this.$el.parent().height()) {
        this.pageableCollection.getNextPage();

      }
    },

    onAttach: function () {
      let pageableCollection = this.pageableCollection;

      this.$el.parent().on('scroll', function () {
        let scrollPosition = Math.ceil($(this).scrollTop() + $(this).outerHeight());
        let divTotalHeight = $(this)[0].scrollHeight
          + parseInt($(this).css('padding-top'), 10)
          + parseInt($(this).css('padding-bottom'), 10)
          + parseInt($(this).css('border-top-width'), 10)
          + parseInt($(this).css('border-bottom-width'), 10);

        if (scrollPosition == divTotalHeight && pageableCollection.hasNextPage())
          pageableCollection.getNextPage();
      });
    }

  });
});
