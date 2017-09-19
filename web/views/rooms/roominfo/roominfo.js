// Filename: /views/rooms/roominfo/roominfo.js

define([
  'underscore',
  'marionette',
], function (_, Marionette) {
  return Marionette.View.extend({
    tagName: 'div',

    className: 'card',

    template: _.template('' +
      '<div class="card-header">' +
      'Room: <%- installation_id %>' +
      '</div>' +
      '<div class="card-body">' +
      '<h4 class="card-title">Instances</h4>\n' +
      '  <div class="card">\n' +
      '  <div class="list-group list-group-flush">\n' +
      '  </div>\n' +
      '</div>' +
      '<br />' +
      '</div>' +
      ''),

    // childView: RoomInstanceListEntry,

    // childViewContainer: '.list-group',

    hasLoaded: false,

    emptyView: function () {
      if (this.hasLoaded)
        return Marionette.View.extend({
          tagName: 'li',
          className: 'list-group-item',
          template: _.template('<div class="d-flex flex-column align-items-center"><p>No instances...<br /><p class="small">Provision the room below.</p></div>')
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
