(function() {

  'use strict';

  angular
    .module('app')
    .controller('HomeController', HomeController);

  HomeController.$inject = ['$scope', '$http', '$rootScope', '$timeout', '$location', '$state', 'API', 'ENUM', 'CONSTANTS', '$window', 'AppAuthenticationService', 'CartModel', 'ConfigModel'];

  function HomeController($scope, $http, $rootScope, $timeout, $location, $state, API, ENUM, CONSTANTS, $window, AppAuthenticationService, CartModel, ConfigModel) {

    var MAX_BANNERS = 10;
    var MAX_NOTICES = 5;
    var MAX_CATEGORIES = 4;
    var MAX_PRODUCTS = 4;
    var MAX_SHOPS = 8;

    $scope.banners = [];
    $scope.notices = [];

    var emptyProduct = {};
    var emptyProducts = [];

    for (var i = 0; i < MAX_PRODUCTS; ++i) {
      emptyProducts.push(emptyProduct);
    }

    // $scope.topSale = emptyProducts;
    $scope.recommendProducts = emptyProducts;
    $scope.newArrival = emptyProducts;
    $scope.editorChoice = emptyProducts;

    $scope.touchSearch = _touchSearch;
    $scope.touchBanner = _touchBanner;
    $scope.touchShop = _touchShop;
    // $scope.touchNotice = _touchNotice;
    $scope.touchCategory = _touchCategory;
    $scope.touchProduct = _touchProduct;
    $scope.touchBrand = _touchBrand;
    $scope.touchGroup = _touchGroup;
    $scope.touchShopList = _touchShopList;
    $scope.touchBarndList = _touchBarndList;

    $scope.reload = _reload;
    // $scope.loadMoreSale = _loadMoreSale;
    $scope.loadMoreProduct = _loadMoreProduct;
    // $scope.touchTopSale = _touchTopSale;

    $scope.isEmpty = false;
    $scope.isLoaded = false;
    $scope.isLoading = false;
    $scope.isLastPage = false;

    $scope.cartModel = CartModel;


    $scope.isB2B2C = false;

    function _touchSearch() {
      $state.go('search', {});
    }

    function _touchBanner(banner) {
      if (!banner.link || !banner.link.length) {
        $scope.toast('没有链接');
        return;
      }

      $window.location.href = banner.link;
    }

    function _touchNotice(notice) {
      var url = '';
      if (notice.url.indexOf("http://", 0) == -1) {
        url = "http://" + notice.url;
      } else {
        url = notice.url;
      }
      $window.location.href = url;
    }

    function _touchGroup(group) {
      $state.go('home', {

      });
    }

    function _touchCategory(category) {
      $state.go('search-result', {
        sortKey: ENUM.SORT_KEY.DEFAULT,
        sortValue: ENUM.SORT_VALUE.DEFAULT,

        keyword: null,
        category: category.id,

        navTitle: category.name,
        navStyle: 'default'
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

    function _touchBarndList() {
      $state.go('recommended-brands', {});
    }

    function _touchShopList() {
      $state.go('recommended-shops', {});
    }

    function _touchShop(recommendShop) {
      $state.go('shop-home', {
        shop: recommendShop.id
      });
    }

    // function _touchTopSale() {
    //   $state.go('search-result', {
    //     sortKey: ENUM.SORT_KEY.SALE,
    //     sortValue: ENUM.SORT_VALUE.DESC,
    //     keyword: null,
    //     navStyle: 'default'
    //   });
    // }

    function _touchProduct(product) {
      $state.go('product', {
        product: product.id,
      });
    }

    function _reloadBanners() {
      API.banner
        .list({
          page: 1,
          per_page: MAX_BANNERS
        })
        .then(function(banners) {
          $scope.banners = banners;
          var timer = $timeout(function() {
            $scope.bannerSwiper = new Swiper('.home-banner', {
              pagination: '.swiper-pagination',
              paginationClickable: true,
              spaceBetween: 30,
              centeredSlides: true,
              autoplay: 1500,
              autoplayDisableOnInteraction: false,
              loop: true,
            });
          }, 1);
        });
    }

    // function _reloadNotices() {
    //   API.notice
    //     .list({
    //       page: 1,
    //       per_page: MAX_NOTICES
    //     })
    //     .then(function(notices) {
    //       $scope.notices = notices;
    //       var timer = $timeout(function() {
    //         $scope.noticeSwiper = new Swiper('.notice-slide', {
    //           spaceBetween: 30,
    //           centeredSlides: true,
    //           autoplay: 1500,
    //           autoplayDisableOnInteraction: false,
    //           direction: 'vertical',
    //           loop: true
    //         });
    //       }, 1);
    //     });
    // }

    // function _reloadCategories() {
    //   API.category
    //     .list({
    //       page: 1,
    //       per_page: MAX_CATEGORIES
    //     })
    //     .then(function(categories) {
    //       $scope.categories = categories;
    //     });
    // }

    function _reloadEditorChoice() {
      API.product
        .list({
          page: 1,
          per_page: MAX_PRODUCTS,
          sort_key: ENUM.SORT_KEY.POPULAR,
          sort_value: ENUM.SORT_VALUE.DESC
        })
        .then(function(products) {
          $scope.editorChoice = products;
        });
    }

    // function _reloadTopSale() {
    //   if ($scope.isLoading)
    //     return;

    //   $scope.topSale = null;
    //   $scope.isLoaded = false;

    //   _fetch(1, MAX_PRODUCTS);
    // }

    // function _loadMoreSale() {
    //   if ($scope.isLoading)
    //     return;
    //   if ($scope.isLastPage)
    //     return;

    //   if ($scope.topSale && $scope.topSale.length) {
    //     _fetch(($scope.topSale.length / MAX_PRODUCTS) + 1, MAX_PRODUCTS);
    //   } else {
    //     _fetch(1, MAX_PRODUCTS);
    //   }
    // }

    function _loadMoreProduct() {
      if ($scope.isLoading)
        return;
      if ($scope.isLastPage)
        return;

      if ($scope.recommendProducts && $scope.recommendProducts.length) {
        _fetch(($scope.recommendProducts.length / MAX_PRODUCTS) + 1, MAX_PRODUCTS);
      } else {
        _fetch(1, MAX_PRODUCTS);
      }
    }

    // function _fetch(page, perPage) {
    //   $scope.isLoading = true;

    //   var params = {};

    //   params.sort_key = ENUM.SORT_KEY.SALE;
    //   params.sort_value = ENUM.SORT_VALUE.DESC;
    //   params.page = page;
    //   params.per_page = perPage;

    //   API.product.list(params).then(function(products) {
    //     $scope.topSale = $scope.topSale ? $scope.topSale.concat(products) : products;
    //     $scope.isEmpty = ($scope.topSale && $scope.topSale.length) ? false : true;
    //     $scope.isLoaded = true;
    //     $scope.isLoading = false;
    //     $scope.isLastPage = (products && products.length < perPage) ? !$scope.isEmpty : false;
    //   });
    // }

    function _fetch(page, perPage) {
      $scope.isLoading = true;

      var params = {};

      params.page = page;
      params.per_page = perPage;

      API.recommend
        .productList(params).then(function(products) {
          $scope.recommendProducts = $scope.recommendProducts ? $scope.recommendProducts.concat(products) : products;
          $scope.isEmpty = ($scope.recommendProducts && $scope.recommendProducts.length) ? false : true;
          $scope.isLoaded = true;
          $scope.isLoading = false;
          $scope.isLastPage = (products && products.length < perPage) ? !$scope.isEmpty : false;
        });
    }

    function _reloadNewArrival() {
      API.product
        .list({
          page: 1,
          per_page: MAX_PRODUCTS,
          sort_key: ENUM.SORT_KEY.DATE,
          sort_value: ENUM.SORT_VALUE.DESC
        })
        .then(function(products) {
          $scope.newArrival = products;
        });
    }

    function _reloadRecommendProductList() {
      if ($scope.isLoading)
        return;

      $scope.recommendProducts = null;
      $scope.isLoaded = false;

      _fetch(1, MAX_PRODUCTS);
    }

    function _reloadRecommendBrandList() {
      API.recommend
        .brandList({
          page: 1,
          per_page: MAX_SHOPS,
        })
        .then(function(brands) {
          $scope.recommendBrands = brands;
        });
    }

    function _reloadRecommendShopList() {
      API.recommend
        .shopList({
          page: 1,
          per_page: MAX_SHOPS,
        })
        .then(function(shops) {
          $scope.recommendShops = shops;
        });
    }

    function _reload() {

      if (CONSTANTS.FOR_WEIXIN && !AppAuthenticationService.getOpenId()) {

        if ($rootScope.isWeixin()) {
          $state.go('wechat-authbase', {});
          return;
        }
      }
      ConfigModel.fetch();
      API.config.get()
        .then(function(data) {
          $scope.isB2B2C = data.platform.type;

          if ($scope.isB2B2C) {
            _reloadBanners();
            _reloadRecommendShopList();
            _reloadRecommendBrandList();
            _reloadRecommendProductList();
            // _reloadCategories();
            // _reloadTopSale();
          } else {
            _reloadBanners();
            // _reloadNotices();
            // _reloadCategories();
            // _reloadEditorChoice();
            // _reloadNewArrival();
            // _reloadTopSale();
            ConfigModel.fetch().then(function() {
              var config = ConfigModel.getConfig();
              var wechat = config['wechat.web'];
              if (wechat && CONSTANTS.FOR_WEIXIN && !AppAuthenticationService.getOpenId()) {
                if ($rootScope.isWeixin()) {
                  $state.go('wechat-authbase', {});
                  return;
                }
              }
              _initShared();
            });
          }
        });




      $scope.cartModel.reloadIfNeeded();
    }

    function _loadMore() {
      // TODO:
    }


    function _initConfig(wechat, url) {

      if (!wechat) {
        return;
      };

      wx.config({
        debug: GLOBAL_CONFIG.DEBUG, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: wechat.app_id, // 必填，公众号的唯一标识
        timestamp: wechat.timestamp, // 必填，生成签名的时间戳
        nonceStr: wechat.nonceStr, // 必填，生成签名的随机串
        signature: wechat.signature, // 必填，签名，见附录1
        jsApiList: ['chooseWXPay',
          'onMenuShareAppMessage',
          'onMenuShareTimeline',
          'onMenuShareAppMessage',
          'onMenuShareQQ'
        ] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
      });

      var shared_link = url;

      wx.ready(function() {
        wx.onMenuShareTimeline({
          title: '推荐分成', // 分享标题
          desc: '',
          link: shared_link, // 分享链接
          imgUrl: '', // 分享图标
          success: function() {
            // 用户确认分享后执行的回调函数
          },
          cancel: function() {
            // 用户取消分享后执行的回调函数
          }
        });

        wx.onMenuShareAppMessage({
          title: '推荐分成', // 分享标题
          desc: '',
          link: shared_link, // 分享链接
          imgUrl: '', // 分享图标
          success: function() {
            // 用户确认分享后执行的回调函数
          },
          cancel: function() {
            // 用户取消分享后执行的回调函数
          }
        });

        wx.onMenuShareQQ({
          title: '推荐分成', // 分享标题
          desc: '', // 分享描述
          link: shared_link, // 分享链接
          imgUrl: '', // 分享图标
          success: function() {
            // 用户确认分享后执行的回调函数
          },
          cancel: function() {
            // 用户取消分享后执行的回调函数
          }
        });
        wx.onMenuShareWeibo({
          title: '推荐分成', // 分享标题
          desc: '', // 分享描述
          link: shared_link, // 分享链接
          imgUrl: '', // 分享图标
          success: function() {
            // 用户确认分享后执行的回调函数
          },
          cancel: function() {
            // 用户取消分享后执行的回调函数
          }
        });

      });

      wx.error(function(res) {
        if (GLOBAL_CONFIG.DEBUG) {
          $rootScope.toast(JSON.stringify(res));
        }
      });

    }

    function _initShared() {

      if ($scope.isB2B2C) { return; };

      if (!AppAuthenticationService.getToken()) {
        return;
      }

      API.bonus.get().then(function(bonus_info) {
        ConfigModel.fetch().then(function() {
          var config = ConfigModel.getConfig();
          var wechat = config['wxpay.web'];
          _initConfig(wechat, bonus_info.shared_link);
          return true;
        });
      });
    }

    _reload();
  }

})();
