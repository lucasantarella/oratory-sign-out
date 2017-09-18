// Filename: /views/devices/devices.js

define([
  'underscore',
  'backbone',
  'marionette',
  'views/devices/deviceslist/deviceslist',
  'views/devices/deviceinfo/deviceinfo',
  'collections/installations'
], function (_, Backbone, Marionette, DevicesListView, DeviceInfoView, InstallationsCollection) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'container no-gutters-sm no-gutters-md',

    template: _.template('<div class="row"><div id="view-devices-list-container" class="col-lg-4 col-md-4 col-sm-6 col-xs-12"></div><div id="view-devices-device-info" class="p-2 col-lg-8 col-md-8 col-sm-6 col-xs-12"></div>'),

    regions: {
      devicesList: '#view-devices-list-container',
      deviceInfo: '#view-devices-device-info'
    },

    initialize: function (options) {
      this.collection = (options.collection) ? options.collection : new InstallationsCollection();

      if (!(options.collection))
        this.collection.fetch();
    },

    onRender: function () {
      this.showChildView('devicesList', new DevicesListView({
        collection: this.collection
      }));
    },

    childViewEvents: {
      'child:click:device': 'onDeviceSelected',
      //'child:click:instance': 'onInstanceSelected'
    },

    onDeviceSelected: function (childView) {
      this.showPanelInfo(childView.model);
    },

    showPanelInfo: function (installation) {
      Backbone.history.navigate('installations/' + installation.get('installation_id'));
      this.showChildView('deviceInfo', new DeviceInfoView({model: installation}));
      this._setChildSelected(installation)
    },

    _setChildSelected: function (model) {
      let devicesView = this.getChildView('devicesList');
      devicesView.children.each(function (e) {
        e.setInactive();
      });
      devicesView.children.findByModel(devicesView.collection.findWhere({installation_id: model.get('installation_id')})).setActive();
    }

  });
});
