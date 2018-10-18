(function () {

	'use strict';

	angular
		.module('app')
		.controller('SearchController', SearchController);

	SearchController.$inject = ['$scope', '$http', '$window', '$timeout', '$rootScope', '$state', 'API', 'ENUM', 'SearchService', 'ConfigModel'];

	function SearchController($scope, $http, $window, $timeout, $rootScope, $state, API, ENUM, SearchService , ConfigModel) {

		$scope.currentKeyword = '';

		$scope.keywords = null;
		$scope.history = SearchService.history;

		$scope.touchSearch = touchSearch;
		$scope.touchKeyword = touchKeyword;
		$scope.showTypeChose = 0;
		$scope.touchTypeChose = _touchTypeChose;
		$scope.hideTypeChose = _hideTypeChose;
		//true：搜索商品  false：搜索店铺
		$scope.isSearchGoods = 1;

		$scope.choseType = _choseType;

    $scope.isB2B2C = ConfigModel.getPlatform().type;

		function touchSearch() {

			if (!$scope.currentKeyword || $scope.currentKeyword.length < 1) {
				$scope.toast('请输入正确的关键字');
				return;
			}

			$rootScope.$emit('searchChanged', $scope.currentKeyword);

			var sort_key = 0;
			var sort_value = 0;

			if($scope.isSearchGoods){
        sort_key = ENUM.SORT_KEY.DEFAULT;
        sort_value = ENUM.SORT_VALUE.DEFAULT;
      } else {
        sort_key = ENUM.SORT_KEY.POPULAR;
        sort_value = ENUM.SORT_VALUE.DESC;
      }

			$state.go('search-result', {

				sortKey: sort_key,
				sortValue: sort_value,

				keyword: $scope.currentKeyword,
				category: null,

        isSearchGoods : $scope.isSearchGoods,

				navTitle: $scope.currentKeyword,
				navStyle: 'search'
			});
		}

		function _touchTypeChose() {
      if ($scope.showTypeChose){
        $scope.showTypeChose = 0;
      } else {
        $scope.showTypeChose = 1;
      }
    }

    function _choseType(type) {
      if(type){
        $scope.isSearchGoods = 1;
      } else {
        $scope.isSearchGoods = 0;
      }
    }

    function _hideTypeChose() {
      $scope.showTypeChose = 0;
    }

		function touchKeyword(keyword) {
			if (!keyword || keyword.length < 1) {
				$scope.toast('请输入正确的关键字');
				return;
			}

			$scope.currentKeyword = keyword;

			$rootScope.$emit('searchChanged', keyword);

			$scope.showTypeChose = 0;


      var sort_key = 0;
      var sort_value = 0;

      if($scope.isSearchGoods){
        sort_key = ENUM.SORT_KEY.DEFAULT;
        sort_value = ENUM.SORT_VALUE.DEFAULT;
      } else {
        sort_key = ENUM.SORT_KEY.POPULAR;
        sort_value = ENUM.SORT_VALUE.DESC;
      }

      $state.go('search-result', {
				sortKey: sort_key,
				sortValue: sort_value,

				keyword: keyword,
				category: null,

        isSearchGoods : $scope.isSearchGoods,

				navTitle: keyword,
				navStyle: 'search'
			});
		}

		function _reloadKeywords() {
			API.search.
			keywordList({})
				.then(function (keywords) {
					$scope.keywords = keywords;
				})
		}

		function _reload() {
			_reloadKeywords();
		}

		_reload();
	}

})();
