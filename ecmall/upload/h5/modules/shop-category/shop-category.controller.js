(function () {

  'use strict';

  angular
    .module('app')
    .controller('ShopCategoryController', ShopCategoryController);

  ShopCategoryController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', '$stateParams', 'API', 'ENUM', 'CartModel'];

  function ShopCategoryController($scope, $http, $window, $timeout, $location, $state, $stateParams, API, ENUM, CartModel) {

  	$scope.shopId = $stateParams.shop;


  	var PER_PAGE = 1000;

	$scope.categories = [];
	$scope.selectedSide = null;

	$scope.touchSide = _touchSide;
	$scope.touchMain = _touchMain;

	$scope.cartModel = CartModel;

	function _touchSide(side) {
		$scope.selectedSide = side;
		$scope.subCategories = side.categories;
	}

	function _touchMain(main) {
		if (!main) {

			var side = $scope.selectedSide;

			$state.go('shop-list', {
				sortKey: ENUM.SORT_KEY.DEFAULT,
				sortValue: ENUM.SORT_VALUE.DEFAULT,

				shop:$scope.shopId,

				keyword: null,
				category: side.id,

				navTitle: side.name,
				showSearch: true,
        		navStype: 'allProduct',
			});

		} else {

			$state.go('shop-list', {
				sortKey: ENUM.SORT_KEY.DEFAULT,
				sortValue: ENUM.SORT_VALUE.DEFAULT,

				shop:$scope.shopId,
				keyword: null,
				category: main.id,

				navTitle: main.name,
				 showSearch: true,
        		navStype: 'allProduct',

			});

		}
	}

	function _reloadCategories() {
		API.category
			.list({
				page: 1,
				per_page: PER_PAGE,
				shop:$scope.shopId
			})
			.then(function (categories) {
				if (categories && categories.length) {
					$scope.categories = categories;
					$scope.selectedSide = categories[0];
					$scope.subCategories = categories[0].categories;
				} else {
					$scope.categories = null;
					$scope.selectedSide = null;
					$scope.subCategories = null;
				}
			});
	}

	function _reload() {
		_reloadCategories();
	}

	_reload();

  }

})();
