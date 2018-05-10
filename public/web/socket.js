define([
  'marionette'
], function (Marionette) {
  return Marionette.Object.extend({

    initialize: function (options) {
      this.url = (options.url) ? options.url : '';
      this.protocols = (options.protocols) ? options.protocols : [];
      this.socket = null;
      this.open();
    },

    isOpen: function () {
      return this.socket !== null && this.socket.readyState === this.socket.OPEN;
    },

    getState: function () {
      return this.socket.readyState
    },

    open: function () {
      this.socket = new WebSocket(this.url, this.protocols);
      let context = this;
      this.socket.onopen = function () {
        context.triggerMethod('open');
      };
      this.socket.onmessage = function (event) {
        context.triggerMethod('message', event)
      };
    },

    close: function () {
      this.socket.close();
      this.socket = null;
    },

    send: function (data) {
      if (this.isOpen())
        return this.socket.send(JSON.stringify(data));
      else
        return false;
    }

  })
});