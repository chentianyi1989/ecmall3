(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('recommended-brands', {
        needAuth: false,
        url:  "/recommended-brands",
        title: "推荐品牌",
        templateUrl: 'modules/recommended-brands/recommended-brands.html'
      });

  }

})();
