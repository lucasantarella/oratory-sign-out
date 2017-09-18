// Filename: /views/devices/deviceslist/deviceslist.js

define([
  'marionette',
  'views/devices/deviceslist/deviceslistitem',
  'css!styles/views/deviceslist/deviceslist.css'
], function (Marionette, DevicesListItem) {
  return Marionette.CollectionView.extend({

    tagName: 'div',

    className: 'list-group p-2',

    childView: DevicesListItem,

    childViewOptions: function (model, index) {
      return {
        model: model,
        childIndex: index
      }
    },

    childViewTriggers: {
      'click:device': 'child:click:device'
    }

  });
});
