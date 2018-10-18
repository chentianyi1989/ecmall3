(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('coupon-select', {
        needAuth: false,
        url:  "/coupon-select?shopId&totalPrice",
        title: "我的优惠券",
        templateUrl: 'modules/coupon-select/coupon-select.html'
      });

  }

})();
