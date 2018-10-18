(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('shop-details', {
        needAuth: false,
        url:  "/shop-details?shopId",
        title: "店铺详情",
        templateUrl: 'modules/shop-details/shop-details.html'
      });

  }

})();
