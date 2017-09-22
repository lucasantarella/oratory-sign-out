// Filename: /views/rooms/roominfo/roominfo.js

define([
    'underscore',
    'marionette',
    'views/rooms/roominfo/roominfostudentslistitem',
], function (_, Marionette, StudentItem) {
    return Marionette.View.extend({

        tagName: 'div',

        className: 'card',

        template: _.template('' +
            '<div class="card-header">' +
            'Room: <%- name %>' +
            '</div>' +
            '<div class="card-body">' +
            '<h4 class="card-title">Students</h4>' +
            '<table class="table">' +
            '  <thead class="thead-default">' +
            '    <tr>' +
            '      <th>#</th>' +
            '      <th>First Name</th>' +
            '      <th>Last Name</th>' +
            '      <th>Email</th>' +
            '    </tr>' +
            '  </thead>' +
            '  <tbody id="view-rooms-students-container">' +
            '  </tbody>' +
            '</table>' +
            '</div>' +
            ''),

        childView: StudentItem,

        childViewContainer: '#view-rooms-students-container',

        hasLoaded: false,

        emptyView: function () {
            if (this.hasLoaded)
                return Marionette.View.extend({
                    tagName: 'tr',
                    attributes: {
                        colspan: '4'
                    },
                    template: _.template('<p>No students...<br /><p class="small">Provision the room below.</p>')
                });
            else
                return Marionette.View.extend({
                    tagName: 'tr',
                    attributes: {
                        colspan: '4'
                    },
                    template: _.template('<p>Loading...</p>')
                });
        },

        initialize: function (options) {
            this.model = options.model;

            let context = this;
            this.collection = options.model.get('students');

            this.collection.on('sync', function () {
                context.hasLoaded = true;
                context.render();
            });

            // this.collection.getFirstPage();
            this.collection.fetch();
        },

        isEmpty: function () {
            return this.collection.length === 0 || !this.hasLoaded;
        },

        onRender: function () {
            // if (this.collection.findWhere({active: true}))
            //   this.getUI('createInstanceButton').hide();
            // else
            //   this.getUI('reprovisionButton').hide();
        },

        onChildClickInstance: function (childView) {
            // Backbone.history.navigate('installations/' + this.model.get('installation_id') + '/instances/' + childView.model.get('uuid'), {trigger: true});
        },

    });
});
