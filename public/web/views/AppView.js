// Filename: /views/AppView.js

define([
  'underscore',
  'marionette'
], function (_, Marionette) {
  return Marionette.View.extend({

    tagName: 'div',

    id: 'main-wrapper',

    template: _.template('' +
      '<main class="oratory-blue">' +
      '<div id="main">' +
      '</div>' +
      '</main>' +
      '<footer class="page-footer white oratory-blue-text">' +
      ' <div class="container">' +
      '   <div class="row">' +
      '   <div class="col s12 valign-wrapper">' +
      '   <span style="width: 100%">© 2018 Copyright <a href="https://www.lucasantarella.com/">Luca Santarella</a> &mdash; All Rights Reserved.</span>' +
      '   <span class="right"><img class="responsive-img" src="./img/logo.svg" width="70px"/></span>' +
      '   </div>' +
      '   </div>' +
      ' </div>' +
      '</footer>' +
      ''),

    regions: {

      main: {
        el: '#main',
        replaceElement: true
      }

    },

  });
});
