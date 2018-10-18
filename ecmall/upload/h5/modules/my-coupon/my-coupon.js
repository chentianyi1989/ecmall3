(function () {

  'use strict';

  angular
    .module('app')
    .config(config);

  config.$inject = ['$stateProvider', '$urlRouterProvider'];

  function config($stateProvider, $urlRouterProvider) {

    $stateProvider
      .state('my-coupon', {
        needAuth: false,
        url:  "/my-coupon",
        title: "我的优惠券",
        templateUrl: 'modules/my-coupon/my-coupon.html'
      });

  }

})();
