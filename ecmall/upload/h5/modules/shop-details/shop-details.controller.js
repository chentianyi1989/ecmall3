(function () {

  'use strict';

  angular
    .module('app')
    .controller('ShopDetailsController', ShopDetailsController);

  ShopDetailsController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', '$stateParams', 'API', 'ENUM'];

  function ShopDetailsController($scope, $http, $window, $timeout, $location, $state, $stateParams, API, ENUM) {

  	$scope.reload = _reload;

  	$scope.shopId = $stateParams.shopId;

  	function _reloadShopInfo() {
		API.shop.shopInfo({
				shop: $scope.shopId
			}).then(function (shop) {
				$scope.shop = shop;
			});
  	}

  	function _reload() {
  		_reloadShopInfo();
  	}

  	_reload();
  }

})();
