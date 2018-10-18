(function () {

    'use strict';

    angular
        .module('app')
        .factory('FileUploadService', FileUploadService);

    FileUploadService.$inject = ['$rootScope', 'FileUploader', 'AppAuthenticationService', 'ENUM'];

    function FileUploadService($rootScope, FileUploader, AppAuthenticationService, ENUM) {

        var service = {};
        service.initUploader = _initUploader;
        service.uploadItem = _uploadItem;
        

        return service; 

        function _initUploader(onSuccessItem) {
            var uploader = new FileUploader({
                url: GLOBAL_CONFIG.API_HOST + '/v2/ecapi.user.avatar.update',
                queueLimit:4
            });
            uploader.autoUpload = true;

            // FILTERS
            uploader.filters.push({
                name: 'imageFilter',
                fn: function (item /*{File|FileLikeObject}*/, options) {
                    var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|',
                        size = item.size;

                    if(size > 1024 * 1024 ){
                        $rootScope.toast('图片大小不能超过1M');
                    }
                    if('|jpg|png|jpeg|bmp|gif|'.indexOf(type) == -1){
                        $rootScope.toast('图片格式错误');
                    }

                    return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1 && size <= 1024 * 1024;
                }
            });


            // CALLBACKS

            uploader.onWhenAddingFileFailed = function (item /*{File|FileLikeObject}*/, filter, options) {
                // console.info('onWhenAddingFileFailed', item, filter, options);
            };
            uploader.onAfterAddingFile = function (fileItem) {
                // console.info('onAfterAddingFile', fileItem);
            };
            uploader.onAfterAddingAll = function (addedFileItems) {
                // console.info('onAfterAddingAll', addedFileItems);
            };
            uploader.onBeforeUploadItem = function (item) {
                item.alias = 'avatar';
                _uploadItem(item);
                // console.info('onBeforeUploadItem', item);
            };
            uploader.onProgressItem = function (fileItem, progress) {
                // console.info('onProgressItem', fileItem, progress);
            };
            uploader.onProgressAll = function (progress) {
                // console.info('onProgressAll', progress);
            };
            //uploader.onSuccessItem = function (fileItem, response, status, headers) {
            //    console.info('onSuccessItem', fileItem, response, status, headers);
            //};

            uploader.onSuccessItem = onSuccessItem;
            uploader.onErrorItem = function (fileItem, response, status, headers) {
                // console.info('onErrorItem', fileItem, response, status, headers);
            };
            uploader.onCancelItem = function (fileItem, response, status, headers) {
                // console.info('onCancelItem', fileItem, response, status, headers);
            };
            uploader.onCompleteItem = function (fileItem, response, status, headers) {
                // console.info('onCompleteItem', fileItem, response, status, headers);
            };

            uploader.onCompleteAll = function () {
                // console.info('onCompleteAll');
            };

            // console.info('uploader', uploader);
            return uploader;
        }

        function _remove(fileItem) {
            upremoveFromQueue
        }
        function _uploadItem(fileItem) {
            var headerUDID = '';
            var headerVer = "1.1.0";
            var headerSign = '';
            var headerToken = AppAuthenticationService.getToken();

            fileItem.headers['X-ECAPI-Ver'] = headerVer;
            fileItem.headers['X-ECAPI-Sign'] = headerSign;
            if (headerToken) {
                fileItem.headers['X-ECAPI-Authorization'] = headerToken;
            }
        };


    }

})();