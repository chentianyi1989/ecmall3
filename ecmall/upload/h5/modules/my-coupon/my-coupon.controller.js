(function () {

  'use strict';

  angular
    .module('app')
    .controller('MyCouponController', MyCouponController);

  MyCouponController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', 'MyCouponModel', '$stateParams', 'API', 'ENUM'];

  function MyCouponController($scope, $http, $window, $timeout, $location, $state, MyCouponModel, $stateParams, API, ENUM) {

  	$scope.AVAILABLE = 0;  // 未过期
  	$scope.EXPIRED = 1;  // 过期
  	$scope.USED = 2;  // 已使用

  	$scope.currentTab = $scope.AVAILABLE;

  	$scope.myCouponModel = MyCouponModel;

	if ($stateParams.tab == 'available') {
		$scope.currentTab = $scope.AVAILABLE;
		$scope.myCouponModel.status = ENUM.COUPON_STATUS.AVAILABLE;
	} else if ($stateParams.tab == 'expired') {
		$scope.currentTab = $scope.EXPIRED;
		$scope.myCouponModel.status = ENUM.COUPON_STATUS.EXPIRED;
	} else if ($stateParams.tab == 'used') {
		$scope.currentTab = $scope.USED;
		$scope.myCouponModel.status = ENUM.COUPON_STATUS.USED;
	} else {
		$scope.currentTab = $scope.AVAILABLE;
		$scope.myCouponModel.status = ENUM.COUPON_STATUS.AVAILABLE;
	}

	$scope.touchTabAvailable = _touchTabAvailable;
	$scope.touchTabExpired = _touchTabExpired;
	$scope.touchTabUsed = _touchTabUsed;

	function _touchTabAvailable() {
		if ($scope.currentTab != $scope.AVAILABLE) {
			$scope.currentTab = $scope.AVAILABLE;
			$scope.myCouponModel.status = ENUM.COUPON_STATUS.AVAILABLE;
			$scope.myCouponModel.reload();
		}
	}

	function _touchTabExpired() {
		if ($scope.currentTab != $scope.EXPIRED) {
			$scope.currentTab = $scope.EXPIRED;
			$scope.myCouponModel.status = ENUM.COUPON_STATUS.EXPIRED;
			$scope.myCouponModel.reload();
		}
	}

	function _touchTabUsed() {
		if ($scope.currentTab != $scope.USED) {
			$scope.currentTab = $scope.USED;
			$scope.myCouponModel.status = ENUM.CASHGIFT_STATUS.USED;
			$scope.myCouponModel.reload();
		}
	}

	$scope.myCouponModel.reload();
  }

})();
