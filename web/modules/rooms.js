//Filename: /modules/authentication.js

define([
  'marionette',
  'views/rooms/rooms',
  'views/instance/instance',
  'models/room',
  'collections/rooms',
  'models/instance',
  'collections/instances',
], function (Marionette, RoomsView, InstanceView, RoomModel, RoomsCollection, InstanceModel, InstancesCollection) {

  return Marionette.AppRouter.extend({

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
      this.rooms = new RoomsCollection();
      this.rooms.fetch();
    },

    routes: {
      '': 'listRooms',
      'rooms': 'listRooms',
      'rooms/:room': 'listRooms',
      // 'rooms/:room/students/:instance_id': 'viewInstance',
    },

    listRooms: function (installation_id) {
      let view = new RoomsView({collection: this.rooms});
      this.app.getView().showChildView('main', view);

      if (installation_id) {
        let model = new RoomModel({installation_id: installation_id});
        this.rooms.add(model);
        model.fetch();
        view.showPanelInfo(model);
      }
    },

    viewInstance: function (installation_id, instance_id) {
      let installation = this.rooms.findWhere({installation_id: installation_id});
      if (installation === undefined) {
        installation = new RoomModel({installation_id: installation_id});
        this.rooms.add(installation);
        installation.fetch();
        installation.get('instances').fetch();
      }

      let instances = installation.get('instances');
      let instance = instances.findWhere({uuid: instance_id});
      if (instance === undefined) {
        instance = new InstanceModel({uuid: instance_id});
        instances.add(instance);
        instance.fetch();
      }

      this.app.getView().showChildView('main', new InstanceView({installation: installation, model: instance}));
    }

  });
});