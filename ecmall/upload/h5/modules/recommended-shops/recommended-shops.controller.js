(function () {

  'use strict';

  angular
    .module('app')
    .controller('RecommendedShopsController', RecommendedShopsController);

  RecommendedShopsController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', '$stateParams', 'API', 'ENUM'];

  function RecommendedShopsController($scope, $http, $window, $timeout, $location, $state, $stateParams, API, ENUM) {

  	$scope.reloadRecommendShopList = _reloadRecommendShopList;
  	$scope.touchShop = _touchShop;

	function _reloadRecommendShopList() {
		API.recommend
			.shopList({
				page: 1,
				per_page: 100,
			})
			.then(function (shops) {
				$scope.recommendShops = shops;
			});
	}

	function _touchShop(recommendShop) {
		$state.go('shop-details', {
			shopId: recommendShop.id
		});			
	}

	_reloadRecommendShopList();
  }

})();
