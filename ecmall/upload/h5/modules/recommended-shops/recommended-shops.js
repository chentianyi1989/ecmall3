(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('recommended-shops', {
        needAuth: false,
        url:  "/recommended-shops",
        title: "推荐店铺",
        templateUrl: 'modules/recommended-shops/recommended-shops.html'
      });

  }

})();
