// Filename: /views/rooms/rooms.js

define([
  'underscore',
  'backbone',
  'marionette',
  'views/rooms/roomslist/roomslist',
  'views/rooms/roominfo/roominfo',
  'collections/rooms'
], function (_, Backbone, Marionette, RoomsListView, RoomInfoView, RoomsCollection) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'container no-gutters-sm no-gutters-md',

    template: _.template('<div class="row"><div id="view-rooms-list-container" class="col-lg-4 col-md-4 col-sm-6 col-xs-12"></div><div id="view-rooms-room-info" class="p-2 col-lg-8 col-md-8 col-sm-6 col-xs-12"></div>'),

    regions: {
      roomsList: '#view-rooms-list-container',
      roomInfo: '#view-rooms-room-info'
    },

    initialize: function (options) {
      this.collection = (options.collection) ? options.collection : new RoomsCollection();
      this.collection.getFirstPage();
    },

    onRender: function () {
      this.showChildView('roomsList', new RoomsListView({
        collection: this.collection
      }));
    },

    childViewEvents: {
      'child:click:room': 'onRoomSelected',
      //'child:click:instance': 'onInstanceSelected'
    },

    onRoomSelected: function (childView) {
      this.showPanelInfo(childView.model);
    },

    showPanelInfo: function (room) {
      Backbone.history.navigate('rooms/' + room.get('name'));
      this.showChildView('roomInfo', new RoomInfoView({model: room}));
      this._setChildSelected(room)
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
