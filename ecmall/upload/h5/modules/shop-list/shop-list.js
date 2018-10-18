(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('shop-list', {
        needAuth: false,
        url:  "/shop-list?sortKey&sortValue&keyword&shop&navTitle&navStype&category&showSearch",
        title: "商品列表",
        templateUrl: 'modules/shop-list/shop-list.html'
      });

  }

})();
