(function () {

  'use strict';

  angular
    .module('app')
    .controller('CouponSelectController', CouponSelectController);

  CouponSelectController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', '$stateParams' , 'ConfirmProductService', 'ConfirmCartService', 'API', 'ENUM', 'CouponSelectModel'];

  function CouponSelectController($scope, $http, $window, $timeout, $location, $state, $stateParams, ConfirmProductService, ConfirmCartService, API, ENUM, CouponSelectModel) {

  	$scope.toucheSelected = _toucheSelected;
  	$scope.toucheUnUserCoupon = _toucheUnUserCoupon;

  	$scope.couponSelectModel = CouponSelectModel;
  	$scope.isSelectCoupon = true;

  	$scope.couponSelectModel.shopId = $stateParams.shopId;
  	$scope.couponSelectModel.totalPrice = $stateParams.totalPrice;

  	$scope.couponSelectModel.reload();

  	function _toucheSelected(target) {
		var coupons = $scope.couponSelectModel.coupons;

		// 设置当前的优惠券为选中  其它的设置为未选中
		for (var i = 0; i < coupons.length; ++i) {
			var coupon = coupons[i];
			if (coupon.id == target.id) {
				coupon.isSelected = true;
				ConfirmProductService.coupon = coupon;
				ConfirmCartService.coupon = coupon;
			}
			else
			{
				coupon.isSelected = false;
			}
		}

		$scope.isSelectCoupon = _isSelectedCoupon();

		$scope.goBack()
  	}

  	function _toucheUnUserCoupon(){
		var coupons = $scope.couponSelectModel.coupons;
		//  把所有的优惠券都设置为没有选中
		for (var i = 0; i < coupons.length; ++i) {
			var coupon = coupons[i];
			coupon.isSelected = false;
		}

		ConfirmProductService.coupon = '';
		ConfirmCartService.coupon = '';

		$scope.isSelectCoupon = _isSelectedCoupon();

		$scope.goBack()
  	}

  	function _isSelectedCoupon(){
		var coupons = $scope.couponSelectModel.coupons;
		//  如果没有选中的  那么就是true
		var isSelected = false;
		for (var i = 0; i < coupons.length; ++i) {
			var coupon = coupons[i];
			if ( coupon.isSelected ) 
			{
				isSelected = true;
			}
		}

		if ( isSelected ) { 
			return true;
		}
		else {
			return false;
		}
  	}
  }

})();
