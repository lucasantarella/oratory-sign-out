// Filename: /views/students/students.js

define([
  'jquery',
  'underscore',
  'backbone',
  'marionette',
  'moment',
  'collections/students',
  'views/students/studentslist'
], function ($, _, Backbone, Marionette, moment, StudentsCollection, StudentsListView) {
  return Marionette.View.extend({

    tagName: 'div',

    className: 'container',

    template: _.template('' +
      '<style type="text/css">' +
      '.collapsible-body {' +
      ' width: 100%;' +
      // ' padding: 0.25rem 2rem 0.25rem 2rem;' +
      '}' +
      '</style>' +
      '<div class="row">' +
      '  <div class="col s12 m8 offset-m2 l6 offset-l3">' +
      '    <div class="card-panel" style="margin-top: 50px;">' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center-align">' +
      '          <h2 style="margin-top: 0.6em;"><%= room %></h2>' +
      '          <h5>Period <%= period %>: <%= start_time %> &ndash; <%= end_time %></h5>' +
      '        </div>' +
      '      </div>' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center">' +
      '          <ul class="collapsible">' +
      '            <li>' +
      '              <div class="collapsible-header"><i class="material-icons red-text">people_outline</i>Signed Out Students</div>' +
      '              <div class="collapsible-body red">' +
      '                <table id="students-signedout-table"></table>' +
      '              </div>' +
      '            </li>' +
      '            <li>' +
      '              <div class="collapsible-header"><i class="material-icons">people</i>Scheduled Students</div>' +
      '              <div class="collapsible-body">' +
      '                <table id="students-scheduled-table"></table>' +
      '              </div>' +
      '            </li>' +
      '            <li>' +
      '              <div class="collapsible-header"><i class="material-icons green-text">directions_walk</i>Incoming Students</div>' +
      '              <div class="collapsible-body">' +
      '                <table id="students-signedin-table"></table>' +
      '              </div>' +
      '            </li>' +
      '          </ul>' +
      '        </div>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>' +
      ''),

    serializeModel: function serializeModel() {
      if (!this.model) {
        return {};
      }
      let data = _.clone(this.model.attributes);
      data.room = data.room.replace('-', ' ');
      if (parseInt(data.room) > 0)
        data.room = 'Room ' + data.room;
      if (data.period === undefined)
        data.period = 0;
      if (data.start_time === undefined)
        data.start_time = '??';
      else
        data.start_time = moment(data.start_time, 'kkmm').format('h:mm A');
      if (data.end_time === undefined)
        data.end_time = '??';
      else
        data.end_time = moment(data.end_time, 'kkmm').format('h:mm A');
      return data;
    },

    ui: {
      'header': 'h2',
      'periodtime': 'h5',
      'collapsible': '.collapsible'
    },

    regions: {
      scheduledStudents: {
        el: '#students-scheduled-table',
        replaceElement: true
      },
      signedinStudents: {
        el: '#students-signedin-table',
        replaceElement: true
      },
      signedoutStudents: {
        el: '#students-signedout-table',
        replaceElement: true
      }
    },

    initialize: function (options) {
      let model = (options.model) ? options.model : new Backbone.Model({room: ''});
      model.url = '/api/teachers/me/schedules/now';
      let collection = (options.collection) ? options.collection : new StudentsCollection();
      let app = options.app;
      let context = this;
      let socket = app.connection;

      socket.on('message', function (event) {
        context.onSocketMessage(event, model, collection)
      });

      this.app = app;
      this.model = model;
      this.model.bind('change', function () {
        context.render.call(context);
      });
      this.collection = collection;
      this.collection.bind('sync', function () {
        context.renderChildren.call(context);
      });
      this.socket = socket;

      model.fetch();
      model.on('sync', function () {
        collection.url = '/api/rooms/' + model.get('room') + '/students';
        collection.fetch();
        model.off('sync', this);
      });
    },

    onSocketMessage: function (event, model, collection) {
      var jsonObject = JSON.parse(event.data);
      switch (jsonObject.data_type) {
        case 'update':
          model.set('name', jsonObject.data.room);
          collection.url = '/api/rooms/' + model.get('room') + '/students';
          collection.fetch();
          break;
      }
    },

    onRender: function () {
      this.collapsible = M.Collapsible.init(this.getUI('collapsible')[0], {accordion: true});
      if (this.model.get('room').length === 0) {
        this.getUI('header').html('No class scheduled!');
        this.getUI('collapsible').hide();
        this.getUI('periodtime').hide();
        return;
      }

      this.getUI('collapsible').show();
      this.getUI('periodtime').show();

      this.renderChildren();
    },

    renderChildren: function () {
      if (this.getChildView('scheduledStudents') !== null && this.getChildView('scheduledStudents') !== undefined)
        this.getChildView('scheduledStudents').render();
      else
        this.showChildView('scheduledStudents', new StudentsListView({
          collection: this.collection,
          filters: ['scheduled']
        }));

      if (this.getChildView('signedinStudents') !== null && this.getChildView('signedinStudents') !== undefined)
        this.getChildView('signedinStudents').render();
      else
        this.showChildView('signedinStudents', new StudentsListView({
          collection: this.collection,
          filters: [
            'signedin_confirmed',
            'signedin_unconfirmed'
          ]
        }));

      if (this.getChildView('signedoutStudents') !== null && this.getChildView('signedoutStudents') !== undefined)
        this.getChildView('signedoutStudents').render();
      else
        this.showChildView('signedoutStudents', new StudentsListView({
          collection: this.collection,
          filters: [
            'signedout_confirmed',
            'signedout_unconfirmed'
          ]
        }));


      if (this.collection.where({status: 'signedout_unconfirmed'}).length > 0 || this.collection.where({status: 'signedout_confirmed'}).length > 0)
        this.collapsible.open(0);

      if (this.collection.where({status: 'signedin_unconfirmed'}).length > 0 || this.collection.where({status: 'signedin_confirmed'}).length > 0)
        this.collapsible.open(2);
    }

  });
});
