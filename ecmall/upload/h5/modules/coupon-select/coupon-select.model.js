(function () {

	'use strict';

	angular
		.module('app')
		.factory('CouponSelectModel', CouponSelectModel);

	CouponSelectModel.$inject = ['$http', '$q', '$timeout', '$rootScope', 'CacheFactory', 'AppAuthenticationService', 'ConfirmCartService', 'API', 'ENUM'];

	function CouponSelectModel($http, $q, $timeout, $rootScope, CacheFactory, AppAuthenticationService, ConfirmCartService, API, ENUM) {

		var PER_PAGE = 10;

		var service = {};

		service.isEmpty = false;
		service.isLoaded = false;
		service.isLoading = false;
		service.isLastPage = false;
		service.status = null;
		service.coupons = null;
		service.fetch = _fetch;
		service.reload = _reload;
		service.loadMore = _loadMore;

		return service;

		function getLocalTime(nS) {
		    var date = new Date(parseInt(nS) * 1000);

		    var Y = date.getFullYear() + '-';
		    var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
		    var D = date.getDate() + ' ';
		    var h = date.getHours() + ':';
		    var m = date.getMinutes() + ':';
		    var s = date.getSeconds(); 

		    return Y+M+D+h+m+s;
		}

		function setupData(coupons) {
	        // 设置当前的促销相关的价格信息
	        for ( var couponIndex in coupons ){
	        	var coupon = coupons[couponIndex];

	        	// 如果已经有选中的，那么选中
	        	if ( ConfirmCartService.coupon ) {
	        		if ( ConfirmCartService.coupon.id == coupon.id ) {
	        			coupon.isSelected = true;
	        		}
	        		else
	        		{
	        			coupon.isSelected = false;
	        		}
	        	}
	        	else
	        	{
	        		coupon.isSelected = false;
	        	}

	        	coupon.start_at = getLocalTime(coupon.start_at);
	        	coupon.end_at = getLocalTime(coupon.end_at);
	        }
		}

		function _reload() {

			if (!AppAuthenticationService.getToken())
				return;

			if (this.isLoading)
				return;

			this.coupons = null;
			this.isEmpty = false;
			this.isLoaded = false;
			this.isLastPage = false;

			this.fetch(1, PER_PAGE);
		}

		function _loadMore() {

			if (!AppAuthenticationService.getToken())
				return;

			if (this.isLoading)
				return;
			if (this.isLastPage)
				return;

			if (this.coupons && this.coupons.length) {
				this.fetch((this.coupons.length / PER_PAGE) + 1, PER_PAGE);
			} else {
				this.fetch(1, PER_PAGE);
			}
		}

		function _fetch(page, perPage) {

			if (!AppAuthenticationService.getToken())
				return;

			this.isLoading = true;

			var params = {
				shop: this.shopId,
				total_price : this.totalPrice,
				page: page,
				per_page: perPage
			};

			var _this = this;
			API.coupon.available(params).then(function (data) {
				var coupons = data.coupons;
				setupData(coupons);
				_this.coupons = _this.coupons ? _this.coupons.concat(coupons) : coupons;
				_this.isEmpty = (_this.coupons && _this.coupons.length) ? false : true;
				_this.isLoaded = true;
				_this.isLoading = false;
				_this.isLastPage = (coupons && coupons.length < perPage) ? !_this.isEmpty : false;
			});
		}

	}

})();