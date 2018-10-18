(function () {

  'use strict';

  angular
    .module('app')
    .controller('ShopHomeController', ShopHomeController);

  ShopHomeController.$inject = ['$scope', '$http', '$window', '$timeout', '$rootScope','$location', '$state', '$stateParams', 'API', 'ENUM', 'AppAuthenticationService'];

  function ShopHomeController($scope, $http, $window, $timeout , $rootScope, $location, $state, $stateParams, API, ENUM,AppAuthenticationService) {

  	$scope.reload = _reload;

	$scope.shopId = $stateParams.shop;

	$scope.showTel = false;

	$scope.touchShopNewProduct = _touchShopNewProduct;
	$scope.touchShopAllProduct = _touchShopAllProduct;
	$scope.touchShopHotProduct = _touchShopHotProduct;
  $scope.touchRecommendProduct = _touchRecommendProduct;
	$scope.touchProduct = _touchProduct;
	$scope.touchLike = _touchLike;
	$scope.touchCallTel = _touchCallTel;
	$scope.touchTelCanel = _touchTelCanel;
	$scope.touchTelSure = _touchTelSure;
  	$scope.touchSearch = _touchSearch;
  	$scope.touchShopCategory = _touchShopCategory;
  	$scope.touchDetails = _touchDetails;

  	function _reloadShopInfo() {
		API.shop.shopInfo({
				shop: $scope.shopId
			}).then(function (shop) {
				$scope.shop = shop;
			});
  	}

  	function _touchShopNewProduct(){
          $state.go('shop-list', {
            sortKey: ENUM.SORT_KEY.DATE,
            sortValue: ENUM.SORT_VALUE.DESC,
            shop: $scope.shopId,
            navTitle: '最新商品',
            navStype: 'newProduct',
          });
      }

      function _touchShopAllProduct(){
         $state.go('shop-list', {
            sortKey: ENUM.SORT_KEY.DEFAULT,
            sortValue: ENUM.SORT_VALUE.DEFAULT,
            shop: $scope.shopId,
            navTitle: '全部商品',
            navStype: 'allProduct',
          });
      }


	  function _touchShopHotProduct(){
	     $state.go('shop-list', {
	        sortKey: ENUM.SORT_KEY.SALE,
	        sortValue: ENUM.SORT_VALUE.DESC,
	        shop: $scope.shopId,
	        navTitle: '热销商品',
	        navStype: 'hotProduct',
	      });
	  }

    function _touchRecommendProduct(){
        $state.go('shop-list', {
            sortKey: ENUM.SORT_KEY.DEFAULT,
            sortValue: ENUM.SORT_VALUE.DEFAULT,
            shop: $scope.shopId,
            navTitle: '推荐商品',
            navStype: 'recommendProduct',
        });
    }

	function _touchDetails() {
      $state.go('shop-details', {
      	shopId: $scope.shopId
      });
	}

    function _touchSearch() {
      if (!$scope.currentKeyword || $scope.currentKeyword.length < 1) {
        $scope.toast('请输入正确的关键字');
        return;
      }

      $rootScope.$emit('searchChanged', $scope.currentKeyword);

      $state.go('shop-list', {
        sortKey: ENUM.SORT_KEY.DEFAULT,
        sortValue: ENUM.SORT_VALUE.DEFAULT,
        shop:$scope.shopId,

        keyword: $scope.currentKeyword,
        category: null,

        navTitle: $scope.currentKeyword,
        showSearch: true,
        navStype: 'allProduct',
      });
    }

    function _touchShopCategory(){
      $state.go('shop-category',{
        shop : $scope.shopId
      })
    }


  	function _touchProduct(product) {
		$state.go('product', {
			product: product.id
		});
	}

	function _touchTelCanel(){
		$scope.showTel = false;
	}

	function _touchTelSure(){
		window.location.href = "tel:"+ $scope.shop.tel;
		$scope.showTel = false;
	}

	function _touchCallTel(){
		$scope.showTel = true;
	}


  	function _reloadRecommendProductList() {
		API.recommend.productList({
				shop: $scope.shopId,
				page: 1,
				per_page: 4
			}).then(function (products) {
				$scope.recommendProducts = products;
			});  		
  	}



  	  function _touchLike(){

        if ( !AppAuthenticationService.getToken() ) {
          $scope.goSignin();
          return;
        }

        if ( $scope.shop.is_watching ) {
            $scope.shop.is_watching = false;
            API.shop
            .shopUnLike({
            	shop: $scope.shopId
            })
            .then(function(is_watching){
              $scope.shop.is_watching = is_watching;
              $scope.toast('取消收藏');
            }, function(error){
              $scope.shop.is_watching = true;
            });
        } else {
            $scope.shop.is_watching = true;
            API.shop
            .shopLike({
            	shop: $scope.shopId
            })
            .then(function(is_watching){
              $scope.shop.is_watching = is_watching;
              $scope.toast('收藏成功');
            }, function(error){
              $scope.shop.is_watching = false;
            });
        }
      }

  	function _reloadNewProducList() {
		var params = {};

		params.shop = $scope.shopId;
		params.sort_key = ENUM.SORT_KEY.DATE;
		params.sort_value = ENUM.SORT_VALUE.DESC;
		params.page = 1;
		params.per_page = 4;

		API.product.list(params).then(function (products) {
			$scope.newProducts = products;
		});
  	}

  	function _reload() {
  		_reloadShopInfo();
  		_reloadRecommendProductList();
  		_reloadNewProducList();
  	}

  	_reload();
  }

})();
