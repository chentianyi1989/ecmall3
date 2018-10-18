(function() {

    'use strict';

    angular
        .module('app')
        .factory('MyFavoriteModel', MyFavoriteModel);

    MyFavoriteModel.$inject = ['$http', '$q', '$timeout', '$rootScope', 'CacheFactory', 'AppAuthenticationService', 'API', 'ENUM'];

    function MyFavoriteModel($http, $q, $timeout, $rootScope, CacheFactory, AppAuthenticationService, API, ENUM) {

        var PER_PAGE = 10;

        var service = {};

        service.isProductEmpty = false;
        service.isProductLoaded = false;
        service.isProductLoading = false;
        service.isProductLastPage = false;
        service.products = null;

        service.isShopEmpty = false;
        service.isShopLoaded = false;
        service.isShopLoading = false;
        service.isShopLastPage = false;
        service.shops = null;

        service.fetchProduct = _fetchProduct;
        service.reloadProduct = _reloadProduct;
        service.loadMoreProduct = _loadMoreProduct;
        service.deleteProduct = _deleteProduct;

        service.fetchShop = _fetchShop;
        service.reloadShop = _reloadShop;
        service.loadMoreShop = _loadMoreShop;
        service.deleteShop = _deleteShop;

        return service;

        function _deleteProduct(productId) {
            if (!AppAuthenticationService.getToken())
                return;

            var _this = this;
            API.product.unlike({
                    product: productId
                })
                .then(function(success) {
                    _this.reloadProduct();
                });
        }

        function _deleteShop(shopID) {
          if (!AppAuthenticationService.getToken())
            return;
          var _this = this;
          API.shop.shopUnLike({
            shop:shopID
          }).then(function(success){
            _this.reloadShop();
          })
        }

        function _reloadProduct() {

            if (!AppAuthenticationService.getToken())
                return;

            if (this.isProductLoading)
                return;

            this.products = null;
            this.isProductEmpty = false;
            this.isProductLoaded = false;
            this.isProductLastPage = false;

            this.fetchProduct(1, PER_PAGE);
        }

        function _reloadShop() {
          if (!AppAuthenticationService.getToken())
            return;
          if (this.isShopLoading)
            return;
          this.shops = null;
          this.isShopEmpty = false;
          this.isShopLoaded = false;
          this.isShopLastPage = false

          this.fetchShop(1,PER_PAGE);
        }

        function _loadMoreProduct() {

            if ( this.isProductLoading )
                return;
            if ( this.isProductLastPage )
                return;

            if (this.products && this.products.length) {
                this.fetchProduct( (this.products.length / PER_PAGE) + 1, PER_PAGE );
            } else {
                this.fetchProduct( 1, PER_PAGE );
            }
        }

        function _loadMoreShop() {
          if(this.isShopLoading)
            return;
          if(this.isShopLastPage)
            return;
          if(this.shops && this.shops.length){
             this.fetchShop( (this.shops.length / PER_PAGE) + 1 , PER_PAGE);
          } else {
              this.fetchShop(1 , PER_PAGE);
          }

        }

        function _fetchProduct( page, perPage ) {

            this.isProductLoading = true;

            var _this = this;
            API.product
                .likedList({
                    page: page,
                    per_page: perPage
                }).then(function(products) {
                    _this.products = _this.products ? _this.products.concat(products) : products;
                    _this.isProductEmpty = (_this.products && _this.products.length) ? false : true;
                    _this.isProductLoaded = true;
                    _this.isProductLoading = false;
                    _this.isProductLastPage = (products && products.length < perPage) ? !_this.isProductEmpty : false;
                });
        }

        function _fetchShop(page,perPage) {
          this.isLoading = true;
          var _this = this;
          API.shop
            .shopFavoriteList({
              page:page,
              per_page:perPage
            }).then(function (shops) {
              _this.shops = _this.shops ? _this.shops.concat(shops): shops;
              _this.isShopEmpty = (_this.shops && _this.shops.length) ? false : true;
              _this.isShopLoaded = true
              _this.isShopLoading = false;
              _this.isShopLastPage = (shops && shops.length < perPage) ? !_this.isShopEmpty : false;

            })


        }

    }

})();
