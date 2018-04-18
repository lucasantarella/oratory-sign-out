// Filename: /views/students/students.js

define([
  'jquery',
  'underscore',
  'backbone',
  'marionette',
  'collections/students',
  'views/students/studentslistitem'
], function ($, _, Backbone, Marionette, StudentsCollection, StudentListItem) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'container',

    template: _.template('' +
      '<div class="row">' +
      '  <div class="col s6 offset-s3">' +
      '    <div class="card-panel" style="margin-top: 50px;">' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center-align">' +
      '          <img class="responsive-img" src="./img/crest.svg" width="100px"/>' +
      '          <h2 style="margin-top: 0.6em;">Room <%= room %></h2>' +
      '        </div>' +
      '      </div>' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center">' +
      '          <table class="table">' +
      '            <thead class="thead-default">' +
      '              <tr><th>Students</th></tr>' +
      '            </thead>' +
      '            <tbody></tbody>' +
      '          </table>' +
      '        </div>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>' +
      ''),

    ui: {
      'header': 'h2'
    },

    initialize: function (options) {
      let model = (options.model) ? options.model : new Backbone.Model({room: ''});
      let collection = (options.collection) ? options.collection : new StudentsCollection();

      this.model = model;
      this.model.bind('change', this.render);
      this.collection = collection;

      let socket = new WebSocket(window.socketUrl, ['teacher']);

      socket.onopen = function () {
        socket.send(JSON.stringify({action: 'get', value: 'currentroom'}))
      };

      socket.onmessage = function (event) {
        var jsonObject = JSON.parse(event.data);
        if (jsonObject.data_type === 'room') {
          model.set(jsonObject.data);
          collection.url = '/api/rooms/' + model.get('room') + '/students';
          collection.fetch();
        }
      };

      this.socket = socket;
    },

    childViewContainer: 'tbody',

    childView: StudentListItem,

    filter: function (child, index, collection) {
      return child.get('status') !== 'signedout';
    },

    onRender: function () {
      if(this.model.get('room').length === 0) {
        this.getUI('header').html('No class scheduled!');
        this.$el.find('table').hide();
      } else
        this.$el.find('table').show();
    },

    onAttach: function () {
      $('body').addClass('oratory-blue');
    },

    onDetach: function () {
      $('body').removeClass('oratory-blue');
      this.socket.close();
    }

  });
});
