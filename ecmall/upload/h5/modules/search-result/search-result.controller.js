(function () {

	'use strict';

	angular
		.module('app')
		.controller('SearchResultController', SearchResultController);

	SearchResultController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', '$stateParams', 'API', 'ENUM','ConfigModel'];

	function SearchResultController($scope, $http, $window, $timeout, $location, $state, $stateParams, API, ENUM,ConfigModel) {

		var PER_PAGE = 10;

		$scope.currentSortKey = $stateParams.sortKey ? $stateParams.sortKey : ENUM.SORT_KEY.DEFAULT;
		$scope.currentSortValue = $stateParams.sortValue ? $stateParams.sortValue : ENUM.SORT_KEY.DESC;
		$scope.currentKeyword = $stateParams.keyword ?$stateParams.keyword:null;
		$scope.currentCategory = $stateParams.category;
		$scope.brand = $stateParams.brand;
		if($stateParams.isSearchGoods){
      $scope.isSearchGoods = parseInt($stateParams.isSearchGoods);
    } else {
      $scope.isSearchGoods = 1;
    }

    $scope.isB2B2C = ConfigModel.getPlatform().type;

		$scope.navTitle = $stateParams.navTitle;
		$scope.navStyle = $stateParams.navStyle;



		if (!$scope.navStyle) {
			$scope.navStyle = 'default';
		}

		$scope.products = null;

		$scope.touchSearch = _touchSearch;
		$scope.touchSortDefault = _touchSortDefault;
		$scope.touchSortSale = _touchSortSale;
		$scope.touchSortDate = _touchSortDate;
		$scope.touchSortPrice = _touchSortPrice;
		$scope.touchSortCredit = _touchSortCredit;
		$scope.touchProduct = _touchProduct;
		$scope.loadMoreProducts = _loadMoreProducts;

		$scope.isProductsEmpty = false;
		$scope.isProductsLoaded = false;
		$scope.isProductsLoading = false;
		$scope.isProductsLastPage = false;

		//多店
    $scope.showTypeChose = 0;
    $scope.touchTypeChose = _touchTypeChose;
    $scope.hideTypeChose = _hideTypeChose;

    $scope.shops = null;
    $scope.isShopsEmpty = false;
    $scope.isShopsLoaded = false;
    $scope.isShopsLoading = false;
    $scope.isShopsLastPage = false;

    $scope.touchShopSortPopular = _touchShopSortPopular;
    $scope.touchShopSortCredit = _touchShopSortCredit;
    $scope.loadMoreShops = _loadMoreShops;
    $scope.touchShop = _touchShop;
    $scope.choseType = _choseType;


		function _touchSearch() {
		  if($scope.isSearchGoods){
        _reloadProducts();
      } else {
		    _reloadShops();
      }

		}
		function _touchSortDefault() {
			var key = ENUM.SORT_KEY.DEFAULT;
			var val = ENUM.SORT_VALUE.DEFAULT;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reloadProducts();
			}
		}

		function _touchSortSale() {
			var key = ENUM.SORT_KEY.SALE;
			var val = ENUM.SORT_VALUE.DESC;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reloadProducts();
			}
		}

		function _touchSortDate() {
			var key = ENUM.SORT_KEY.DATE;
			var val = ENUM.SORT_VALUE.DESC;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reloadProducts();
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
				_reloadProducts();
			}
		}

		function _touchSortCredit() {
			var key = ENUM.SORT_KEY.CREDIT;
			var val = ENUM.SORT_VALUE.DESC;
			if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
				$scope.currentSortKey = key;
				$scope.currentSortValue = val;
				_reloadProducts();
			}
		}

		function _touchProduct(product) {
			$state.go('product', {
				product: product.id
			});
		}

		function _reloadProducts() {
			if ($scope.isProductsLoading)
				return;
	        $scope.products = null;
	        $scope.isProductsEmpty = false;
	        $scope.isProductsLoaded = false;
	        _fetchProducts(1, PER_PAGE);
		}

		function _loadMoreProducts() {
			if ($scope.isProductsLoading)
				return;
			if ($scope.isProductsLastPage)
				return;

			if ($scope.products && $scope.products.length) {
				_fetchProducts(($scope.products.length / PER_PAGE) + 1, PER_PAGE);
			} else {
				_fetchProducts(1, PER_PAGE);
			}
		}

		function _fetchProducts(page, perPage) {
			$scope.isProductsLoading = true;

			var params = {};

			if($scope.currentCategory){
				params.category = $scope.currentCategory;
			}

			if($scope.currentKeyword){
				params.keyword = $scope.currentKeyword;
			}

			if ($scope.brand) {
				params.brand = $scope.brand;
			}

			params.sort_key = $scope.currentSortKey;
			params.sort_value = $scope.currentSortValue;
			params.page = page;
			params.per_page = perPage;


			API.product.list(params).then(function (products) {
				$scope.products = $scope.products ? $scope.products.concat(products) : products;
				$scope.isProductsEmpty = ($scope.products && $scope.products.length) ? false : true;
				$scope.isProductsLoaded = true;
				$scope.isProductsLoading = false;
				$scope.isProductsLastPage = (products && products.length < perPage) ? !$scope.isProductsEmpty : false;
			});
		}

		//多店
    function _touchTypeChose() {
      if ($scope.showTypeChose){
        $scope.showTypeChose = 0;
      } else {
        $scope.showTypeChose = 1;
      }
    }

    function _hideTypeChose() {
      $scope.showTypeChose = 0;
    }

    function _reloadShops() {
      if ($scope.isShopsLoading)
        return;

      $scope.shops = null;
      $scope.isShopsEmpty = false;
      $scope.isShposLoaded = false;
      _fetchShops(1, PER_PAGE);
    }

    function _fetchShops(page, perPage) {
      $scope.isShopsLoading = true;

      var params = {};

      if($scope.currentCategory){
        params.category = $scope.currentCategory;
      }

      if($scope.currentKeyword){
        params.keyword = $scope.currentKeyword;
      }

      if ($scope.brand) {
        params.brand = $scope.brand;
      }

      params.sort_key = $scope.currentSortKey;
      params.sort_value = $scope.currentSortValue;
      params.page = page;
      params.per_page = perPage;


      API.shop.shopList(params).then(function (shops) {
        $scope.shops = $scope.shops ? $scope.shops.concat(shops) : shops;
        $scope.isShopsEmpty = ($scope.shops && $scope.shops.length) ? false : true;
        $scope.isShopsLoaded = true;
        $scope.isShopsLoading = false;
        $scope.isShopsLastPage = (shops && shops.length < perPage) ? !$scope.isShopsEmpty : false;
      });
    }

    function _touchShopSortPopular() {
      var key = ENUM.SORT_KEY.POPULAR;
      var val = ENUM.SORT_VALUE.DESC;
      if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
        $scope.currentSortKey = key;
        $scope.currentSortValue = val;
        _reloadShops();
      }
    }


    function _loadMoreShops() {
      if ($scope.isShopsLoading)
        return;
      if ($scope.isShposLastPage)
        return;

      if ($scope.shops && $scope.shops.length) {
        _fetchProducts(($scope.shops.length / PER_PAGE) + 1, PER_PAGE);
      } else {
        _fetchProducts(1, PER_PAGE);
      }
    }


    function _touchShopSortCredit() {
      var key = ENUM.SORT_KEY.CREDIT;
      var val = ENUM.SORT_VALUE.DESC;
      if (key != $scope.currentSortKey || val != $scope.currentSortValue) {
        $scope.currentSortKey = key;
        $scope.currentSortValue = val;
        _reloadShops();
      }
    }

    function _touchShop(shop) {
      $state.go('shop-home', {
        shop: shop.id
      });
    }

    function _choseType(type) {
      if(type){
        $scope.isSearchGoods = 1;
        _reloadProducts();
      } else {
        $scope.isSearchGoods = 0;
        _reloadShops();
      }
    }

    if($scope.isSearchGoods){
      _reloadProducts();
    } else{
      _reloadShops();
    }

	}

})();
