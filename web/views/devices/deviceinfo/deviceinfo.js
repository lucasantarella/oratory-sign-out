// Filename: /views/devices/deviceinfo/deviceinfo.js

define([
  'underscore',
  'marionette',
  'views/devices/deviceinfo/deviceinstancelistentry'
], function (_, Marionette, DeviceInstanceListEntry) {
  return Marionette.CompositeView.extend({
    tagName: 'div',

    className: 'card',

    template: _.template('' +
      '<div class="card-header">' +
      'Device: <%- installation_id %>' +
      '</div>' +
      '<div class="card-body">' +
      '<h4 class="card-title">Instances</h4>\n' +
      '  <div class="card">\n' +
      '  <div class="list-group list-group-flush">\n' +
      '  </div>\n' +
      '</div>' +
      '<br />' +
      '<div class="d-flex">' +
      '<a href="#" class="btn btn-success"><i class="fa fa-plus"></i>&nbsp;&nbsp;Provision Device</a>' +
      '<a href="#" class="btn btn-warning ml-auto"><i class="fa fa-warning"></i>&nbsp;&nbsp;Re-Provision Device</a>' +
      '</div>' +
      '</div>' +
      ''),

    childView: DeviceInstanceListEntry,

    childViewContainer: '.list-group',

    ui: {
      'createInstanceButton': '.btn-success',
      'reprovisionButton': '.btn-warning'
    },

    events: {
      'click @ui.createInstanceButton': 'onClickCreateInstance',
      'click @ui.reprovisionButton': 'onClickReprovision'
    },

    childViewTriggers: {
      'click:instance': 'child:click:instance'
    },

    hasLoaded: false,

    emptyView: function () {
      if (this.hasLoaded)
        return Marionette.View.extend({
          tagName: 'li',
          className: 'list-group-item',
          template: _.template('<div class="d-flex flex-column align-items-center"><p>No instances...<br /><p class="small">Provision the device below.</p></div>')
        });
      else
        return Marionette.View.extend({
          tagName: 'li',
          className: 'list-group-item',
          template: _.template('<div class="d-flex align-items-center">Loading...</div>')
        });
    },

    initialize: function (options) {
      this.model = options.model;

      let context = this;
      this.collection = options.model.get('instances');

      this.collection.on('sync', function () {
        context.hasLoaded = true;
        context.render();
      });

      this.collection.fetch();
    },

    isEmpty: function () {
      return this.collection.length === 0 || !this.hasLoaded;
    },

    onRender: function () {
      if (this.collection.findWhere({active: true}))
        this.getUI('createInstanceButton').hide();
      else
        this.getUI('reprovisionButton').hide();
    },

    onClickCreateInstance: function (event) {
      event.preventDefault();
      this.collection.create({
        installation_id: this.model.get('installation_id')
      });
    },

    onClickReprovision: function (event) {
      event.preventDefault();
      let model = this.collection.findWhere({active: true});
      model.set('active', false);
      model.save();
      this.collection.create({
        installation_id: this.model.get('installation_id')
      });
    },

    onChildClickInstance: function (childView) {
      Backbone.history.navigate('installations/' + this.model.get('installation_id') + '/instances/' + childView.model.get('uuid'), {trigger: true});
    },

  });
});
