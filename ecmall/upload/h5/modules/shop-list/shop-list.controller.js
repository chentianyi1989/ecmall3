(function () {

  'use strict';

  angular
    .module('app')
    .controller('ShopListController', ShopListController);

  ShopListController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', '$stateParams', 'API', 'ENUM'];

  function ShopListController($scope, $http, $window, $timeout, $location, $state, $stateParams, API, ENUM) {

  		var PER_PAGE = 10;

		$scope.currentSortKey = $stateParams.sortKey ? $stateParams.sortKey : ENUM.SORT_KEY.DEFAULT;
		$scope.currentSortValue = $stateParams.sortValue ? $stateParams.sortValue : ENUM.SORT_KEY.DESC;
		$scope.currentKeyword = $stateParams.keyword ?$stateParams.keyword:null;
		$scope.currentCategory = $stateParams.category;
		$scope.shop = $stateParams.shop;
		$scope.navTitle = $stateParams.navTitle;
		$scope.navStype = $stateParams.navStype;
		$scope.showSearch = $stateParams.showSearch;

		$scope.touchSortDefault = _touchSortDefault;
		$scope.touchSortSale = _touchSortSale;
		$scope.touchSortDate = _touchSortDate;
		$scope.touchSortPrice = _touchSortPrice;
		$scope.touchSortCredit = _touchSortCredit;
		$scope.touchProduct = _touchProduct;
		$scope.loadMore = _loadMore;

		$scope.isEmpty = false;
		$scope.isLoaded = false;
		$scope.isLoading = false;
		$scope.isLastPage = false;
		$scope.isShowFilter = false;

		function _reload() {
			if ($scope.isLoading)
				return;

			$scope.products = null;
			$scope.isEmpty = false;
			$scope.isLoaded = false;

			if ($scope.navStype == 'allProduct') {
				$scope.isShowFilter = true;
			} else {
				$scope.isShowFilter = false;
			}

			_fetch(1, PER_PAGE);
		}

		function _touchSearch() {
			_reload();
		}

		function _loadMore() {
			if ($scope.isLoading)
				return;
			if ($scope.isLastPage)
				return;

			if ($scope.products && $scope.products.length) {
				_fetch(($scope.products.length / PER_PAGE) + 1, PER_PAGE);
			} else {
				_fetch(1, PER_PAGE);
			}
		}

			function _touchSortDefault() {
			var key = ENUM.SORT_KEY.DEFAULT;
			var val = ENUM.SORT_VALUE.DEFAULT;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reload();
			}
		}

		function _touchSortSale() {
			var key = ENUM.SORT_KEY.SALE;
			var val = ENUM.SORT_VALUE.DESC;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reload();
			}
		}

		function _touchSortDate() {
			var key = ENUM.SORT_KEY.DATE;
			var val = ENUM.SORT_VALUE.DESC;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reload();
			}
		}

		function _touchSortPrice() {
			var key = ENUM.SORT_KEY.PRICE;
			var val = ENUM.SORT_VALUE.DESC;

			if ($scope.currentSortKey == ENUM.SORT_KEY.PRICE) {
				if ($scope.currentSortValue == ENUM.SORT_VALUE.DEFAULT || $scope.currentSortValue == ENUM.SORT_VALUE.ASC) {
					key = ENUM.SORT_KEY.PRICE;
					val = ENUM.SORT_VALUE.DESC;
				} else {
					key = ENUM.SORT_KEY.PRICE;
					val = ENUM.SORT_VALUE.ASC;
				}
			} else {
				key = ENUM.SORT_KEY.PRICE;
				val = ENUM.SORT_VALUE.DESC;
			}

			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reload();
			}
		}

		function _touchSortCredit() {
			var key = ENUM.SORT_KEY.CREDIT;
			var val = ENUM.SORT_VALUE.DESC;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reload();
			}
		}

		function _touchProduct(product) {
			$state.go('product', {
				product: product.id
			});
		}



		function _fetch(page, perPage) {
			$scope.isLoading = true;

			var params = {};

			if($scope.currentCategory){
				params.category = $scope.currentCategory;
			}

			if($scope.currentKeyword){
				params.keyword = $scope.currentKeyword;
			}

			params.sort_key = $scope.currentSortKey;
			params.sort_value = $scope.currentSortValue;
			params.page = page;
			params.per_page = perPage;
			params.shop = $scope.shop;

			// params.brand = $scope.brand;

			if ($scope.navStype == 'recommendProduct') {
				API.recommend.productList(params).then(function (products) {
					$scope.products = $scope.products ? $scope.products.concat(products) : products;
					$scope.isEmpty = ($scope.products && $scope.products.length) ? false : true;
					$scope.isLoaded = true;
					$scope.isLoading = false;
					$scope.isLastPage = (products && products.length < perPage) ? !$scope.isEmpty : false;
				});  		
			} else {
				API.product.list(params).then(function (products) {
					$scope.products = $scope.products ? $scope.products.concat(products) : products;
					$scope.isEmpty = ($scope.products && $scope.products.length) ? false : true;
					$scope.isLoaded = true;
					$scope.isLoading = false;
					$scope.isLastPage = (products && products.length < perPage) ? !$scope.isEmpty : false;
				});
			}
			
		}

		_reload();

  }

})();
