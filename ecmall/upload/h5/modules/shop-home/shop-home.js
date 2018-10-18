(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('shop-home', {
        needAuth: false,
        url:  "/shop-home?shop",
        title: "店铺首页",
        templateUrl: 'modules/shop-home/shop-home.html'
      });

  }

})();
