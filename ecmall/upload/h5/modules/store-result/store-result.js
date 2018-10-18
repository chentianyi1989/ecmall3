(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('store-result', {
        needAuth: false,
        url:  "/store-result",
        title: "店铺搜索结果",
        templateUrl: 'modules/store-result/store-result.html'
      });

  }

})();
