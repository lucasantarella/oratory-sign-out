// Filename: /views/instance/instance.js

define([
  'underscore',
  'marionette',
  'views/instance/instancelocationitem',
  'async!https://maps.google.com/maps/api/js?key=AIzaSyAI13Ve6CmlA40nURd6fYIUYzpsmDxZjxw'
], function (_, Marionette, LocationView) {
  return Marionette.CompositeView.extend({

    tagName: 'div',

    className: 'card m-2',

    template: _.template('' +
      '<div class="card-header d-flex align-items-center justify-left">' +
      '<span class="">Instance UUID: <%- uuid %></span>' +
      '<a href="#" id="destroy-session-button" class="btn badge badge-danger text-white ml-auto"><i class="fa fa-warning"></i>&nbsp;Kill Session</a>' +
      '</div>' +
      '<div class="card-img-top" id="map" style="height: calc(60vh);">' +
      '</div>' +
      '</div>' +
      '<div class="card-body">' +
      '<div class="list-group">' +
      '</div>' +
      '</div>' +
      '<div class="card-footer d-flex align-items-center justify-left">' +
      ' <a href="#" id="request-location-button" class="btn badge badge-success text-white ml-auto"><i class="fa fa-map-marker"></i>&nbsp;&nbsp;Request Location</a>' +
      '</div>' +
      ''),

    childViewContainer: '.list-group',

    childView: LocationView,

    emptyView: Marionette.View.extend({

      tagName: 'div',

      className: 'list-group-item d-flex align-items-center justify-content-center bg-info text-white',

      template: _.template('No locations reported!')

    }),

    ui: {
      map: '#map',
      requestLocationButton: '#request-location-button',
      destroySessionButton: '#destroy-session-button'
    },

    events: {
      'click @ui.requestLocationButton': 'onClickRequestLocation',
      'click @ui.destroySessionButton': 'onClickDestroySession'
    },

    initialize: function (options) {
      this.installation = options.installation;
      this.model = options.model;
      this.collection = this.model.get('locations');
      this.collection.fetch();
      this.collection.on('sync', this.render);
      this.markers = [];
    },

    onRender: function () {
      this.map = new google.maps.Map(this.getUI('map').get(0), {
        center: {lat: 39, lng: -95},
        zoom: 3
      });

      if (this.collection.length > 0) {
        let avgLat = _.reduce(this.collection.pluck("lat"), function (memo, num) {
          return memo + num;
        }, 0) / this.collection.length;

        let avgLon = _.reduce(this.collection.pluck("lon"), function (memo, num) {
          return memo + num;
        }, 0) / this.collection.length;

        // Refocus the map
        this.map.setCenter({
          lat: avgLat,
          lng: avgLon
        });

        // Zoom to bounds
        let bound = new google.maps.LatLngBounds();

        this.collection.each(function (e) {
          let pos = {lat: e.get('lat'), lng: e.get('lon')};
          this.markers.push(new google.maps.Marker({
            position: pos,
            map: this.map
          }));
          bound.extend(pos);
        }, this);

        this.map.fitBounds(bound);
      }
    },

    onAttach: function () {
      this.timer =
        setInterval(() => {
          this.collection.fetch();
        }, 60000);
    },

    onDetach: function () {
      clearInterval(this.timer);
    },

    onClickRequestLocation: function (event) {
      event.preventDefault();
      console.log('onRequestLocation with installation_id: ' + this.installation.get('installation_id'));
      this.installation.requestLocationReport({
        success: function () {
          console.log('Location captured and reported...');
          setTimeout(() => {
            this.collection.fetch();
          }, 3000); // Fetch after a few seconds
        }
      }, this)
    },

    onClickDestroySession: function (event) {
      event.preventDefault();
      this.installation.deprovision({
        success: function () {
          Backbone.history.navigate('/installations/' + this.installation.get('installation_id'), {trigger: true});
        }
      }, this)
    }

  });
});
