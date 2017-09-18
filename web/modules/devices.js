//Filename: /modules/authentication.js

define([
  'marionette',
  'views/devices/devices',
  'views/instance/instance',
  'models/installation',
  'collections/installations',
  'models/instance',
  'collections/instances',
], function (Marionette, DevicesView, InstanceView, InstallationModel, InstallationsCollection, InstanceModel, InstancesCollection) {

  return Marionette.AppRouter.extend({

    initialize: function (options) {
      this.app = (options.app) ? options.app : null;
      this.installations = new InstallationsCollection();
      this.installations.fetch();
    },

    routes: {
      '': 'listDevices',
      'installations': 'listDevices',
      'installations/:installation_id': 'listDevices',
      'installations/:installation_id/instances/:instance_id': 'viewInstance',
    },

    listDevices: function (installation_id) {
      let view = new DevicesView({collection: this.installations});
      this.app.getView().showChildView('main', view);

      if (installation_id) {
        let model = new InstallationModel({installation_id: installation_id});
        this.installations.add(model);
        model.fetch();
        view.showPanelInfo(model);
      }
    },

    viewInstance: function (installation_id, instance_id) {
      let installation = this.installations.findWhere({installation_id: installation_id});
      if (installation === undefined) {
        installation = new InstallationModel({installation_id: installation_id});
        this.installations.add(installation);
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