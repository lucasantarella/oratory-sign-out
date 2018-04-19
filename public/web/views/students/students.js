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
      '  <div class="col s12 m8 offset-m2 l6 offset-l3">' +
      '    <div class="card-panel" style="margin-top: 50px;">' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center-align">' +
      '          <img class="responsive-img" src="./img/crest.svg" width="100px"/>' +
      '          <h2 style="margin-top: 0.6em;">Room <%= room %></h2>' +
      '        </div>' +
      '      </div>' +
      '      <div class="row">' +
      '        <div class="col s10 offset-s1 center">' +
      '          <ul class="collapsible">' +
      '            <li>' +
      '              <div class="collapsible-header"><i class="material-icons red-text">people_outline</i>Signed Out Students</div>' +
      '              <div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>' +
      '            </li>' +
      '            <li>' +
      '              <div class="collapsible-header"><i class="material-icons">people</i>Scheduled Students</div>' +
      '              <div class="collapsible-body">' +
      '                <table class="table">' +
      '                  <tbody></tbody>' +
      '                </table>' +
      '              </div>' +
      '            </li>' +
      '            <li>' +
      '              <div class="collapsible-header"><i class="material-icons green-text">directions_walk</i>Incoming Students</div>' +
      '              <div class="collapsible-body"><span>Lorem ipsum dolor sit amet.</span></div>' +
      '            </li>' +
      '          </ul>' +
      '        </div>' +
      '      </div>' +
      '    </div>' +
      '  </div>' +
      '</div>' +
      ''),

    ui: {
      'header': 'h2',
      'collapsible': '.collapsible'
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
      let instance = M.Collapsible.init(this.getUI('collapsible')[0]);
      if(this.model.get('room').length === 0) {
        this.getUI('header').html('No class scheduled!');
        this.getUI('collapsible').hide();
      } else
        this.getUI('collapsible').show();

    },

    onAttach: function () {
      $('body').addClass('oratory-blue');
      // let el = this.$el.find('ul').collapsible();

    },

    onDetach: function () {
      $('body').removeClass('oratory-blue');
      this.socket.close();
    }

  });
});
