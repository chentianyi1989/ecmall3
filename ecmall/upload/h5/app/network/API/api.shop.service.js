(function () {
    'use strict';

    angular
    .module('app')
    .factory('APIShopService', APIShopService);

    APIShopService.$inject = ['$http', '$q', '$timeout', 'CacheFactory', 'ENUM'];

    function APIShopService($http, $q, $timeout, CacheFactory, ENUM) {

        var service = new APIEndpoint( $http, $q, $timeout, CacheFactory, 'APIShopService' );
        service.shopInfo = _shopInfo;
        service.shopLike = _shopLike;
        service.shopUnLike = _shopUnLike;
        service.shopFavoriteList = _shopFavoriteList;
        service.shopList = _shopList;
        return service;


        function _shopInfo(params) {
            return this.fetch( '/v2/ecapi.shop.get', params, false, function(res){
                return (res.data &&(ENUM.ERROR_CODE.OK == res.data.error_code)) ? res.data.shop : null;

            });
        }

        function _shopLike(params){
            return this.fetch( '/v2/ecapi.shop.watch' ,params , false , function(res){
                return(res.data && (ENUM.ERROR_CODE.OK == res.data.error_code)) ? res.data.is_watching : null;
            });
        }

         function _shopUnLike(params){
            return this.fetch( '/v2/ecapi.shop.unwatch' ,params , false , function(res){
                return(res.data && (ENUM.ERROR_CODE.OK == res.data.error_code)) ? res.data.is_watching : null;
            });
        }

        function _shopFavoriteList(params) {
           return this.fetch( '/v2/ecapi.shop.watching.list' ,params , false , function (res) {
              return(res.data && (ENUM.ERROR_CODE.OK == res.data.error_code)) ? res.data.shops : null;
           });

        }

        function _shopList(params) {
          return this.fetch('/v2/ecapi.shop.list' , params , false , function (res) {
              return(res.data && (ENUM.ERROR_CODE.OK == res.data.error_code)) ? res.data.shops : null;
          })
        }


    }

})();
