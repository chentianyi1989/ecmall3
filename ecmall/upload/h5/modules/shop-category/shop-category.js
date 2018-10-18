(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('shop-category', {
        needAuth: false,
        url:  "/shop-category?shop",
        title: "商品分类",
        templateUrl: 'modules/shop-category/shop-category.html'
      });

  }

})();
