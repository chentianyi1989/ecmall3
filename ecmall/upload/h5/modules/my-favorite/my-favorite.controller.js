(function () {

    'use strict';

    angular
        .module('app')
        .controller('MyFavoriteController', MyFavoriteController);

    MyFavoriteController.$inject = ['$scope', '$state', 'API', 'ENUM', 'MyFavoriteModel' , 'ConfigModel'];

    function MyFavoriteController($scope, $state, API, ENUM, MyFavoriteModel , ConfigModel) {

      $scope.myFavoriteModel = MyFavoriteModel;

      $scope.isB2B2C = ConfigModel.getPlatform().type;

      $scope.showShopList = false;
      $scope.showProductList = true;


      $scope.touchProduct = _touchProduct;
      $scope.touchProductDelete = _touchProductDelete;
      $scope.touchShopType = _touchShopType;
      $scope.touchProductType = _touchProductType;

      $scope.touchShop = _touchShop;
      $scope.touchShopDelete = _touchShopDelete;

      function _touchProduct( product ) {
        $state.go('product', { product:product.id });
      }

      function _touchShop( shop){
        $state.go('shop-home' , {shop:shop.id})
      }

      function _touchProductDelete( product ) {
        $scope.myFavoriteModel.deleteProduct( product.id );
      }

      function _touchShopDelete(shop) {
        $scope.myFavoriteModel.deleteShop(shop.id);
      }


      function _touchProductType(){
        $scope.showShopList = false;
        $scope.showProductList = true;
        $scope.myFavoriteModel.reloadProduct();

      }

      function _touchShopType() {
        $scope.showShopList = true;
        $scope.showProductList = false;
        $scope.myFavoriteModel.reloadShop();
      }

      $scope.myFavoriteModel.reloadProduct();
    }

})();
