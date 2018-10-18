(function () {

	'use strict';

	angular
		.module('app')
		.factory('MyCouponModel', MyCouponModel);

	MyCouponModel.$inject = ['$http', '$q', '$timeout', '$rootScope', 'CacheFactory', 'AppAuthenticationService', 'API', 'ENUM'];

	function MyCouponModel($http, $q, $timeout, $rootScope, CacheFactory, AppAuthenticationService, API, ENUM) {

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
	        	coupon.start_at = getLocalTime(coupon.start_at);
	        	coupon.end_at = getLocalTime(coupon.end_at);

	        	if ( coupon.status == ENUM.COUPON_STATUS.AVAILABLE ) {
	        		// 未过期
	        		coupon.statusStrig = '可使用';
	        	}
	        	else if ( coupon.status == ENUM.COUPON_STATUS.EXPIRED ) {
	        		// 过期
	        		coupon.statusStrig = '已过期';
	        	}
	        	else if ( coupon.status == ENUM.COUPON_STATUS.USED ) {
	        		// 已使用
	        		coupon.statusStrig = '已使用';
	        	}
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
				page: page,
				per_page: perPage
			};

			params.status = this.status;

			var _this = this;
			API.coupon.list(params).then(function (coupons) {
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