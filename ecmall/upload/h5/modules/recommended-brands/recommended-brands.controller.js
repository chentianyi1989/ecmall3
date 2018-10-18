(function () {

  'use strict';

  angular
    .module('app')
    .controller('RecommendedBrandsController', RecommendedBrandsController);

  RecommendedBrandsController.$inject = ['$scope', '$http', '$window', '$timeout', '$location', '$state', '$stateParams', 'API', 'ENUM'];

  function RecommendedBrandsController($scope, $http, $window, $timeout, $location, $state, $stateParams, API, ENUM) {

  	$scope.reloadRecommendBrandList = _reloadRecommendBrandList;
  	$scope.touchBrand = _touchBrand;

	function _reloadRecommendBrandList() {
		API.recommend
			.brandList({
				page: 1,
				per_page: 100,
			})
			.then(function (brands) {
				$scope.recommendBrands = brands;
			});
	}

	function _touchBrand(recommendBrand) {
		$state.go('search-result', {
			sortKey: ENUM.SORT_KEY.DEFAULT,
			sortValue: ENUM.SORT_VALUE.DEFAULT,

			keyword: null,
			brand: recommendBrand.id,

			navStyle: 'default'
		});			
	}

	_reloadRecommendBrandList();
  }

})();
