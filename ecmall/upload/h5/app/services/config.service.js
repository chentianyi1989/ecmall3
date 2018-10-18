(function() {

    'use strict';

    angular
        .module('app')
        .factory('ConfigModel', ConfigModel);

    ConfigModel.$inject = ['API','$cookies'];

    function ConfigModel( API,$cookies) {

        var service = {};

        try{
            service.config = $cookies.getObject( 'c' );
            service.feature = $cookies.getObject( 'f' );
            service.platform = $cookies.getObject( 'p');
        }
        catch(e)
        {
            $cookies.remove( 'c' );
            $cookies.remove( 'f' );
            $cookies.remove( 'p' );
        }

        service.fetch = _fetch;
        service.getConfig = _getConfig;
        service.getFeature = _getFeature;
        service.getPlatform = _getPlatform;

        return service;

        function _fetch() {
            var _this = this;
           return API.config.get()
                .then(function (data) {
                    _this.config = data.config;
                    _this.feature = data.feature;
                    _this.platform = data.platform;
                    _saveConfig(data.config);
                    _saveFeature(data.feature);
                    _savePlatform(data.platform);

                    return data.config;
                });
        }

        function _saveFeature(feature) {
          $cookies.remove( 'f' );
        	var exdate=new Date();
        	exdate.setDate(exdate.getDate()+7);
        	$cookies.putObject( 'f', feature , {'expires': exdate});
        }

        function _saveConfig(config){
          $cookies.remove( 'c' );
            // save to cookie storage
            var exdate=new Date();
            exdate.setDate(exdate.getDate()+7);
            $cookies.putObject( 'c', config , {'expires': exdate});
        }

        function _savePlatform(platform){
          $cookies.remove( 'p' );
            var exdate = new Date;
            exdate.setDate(exdate.getDate()+7);
            $cookies.putObject( 'p' , platform , {'expires' : exdate})
        }

        function _getFeature(){
        	if (this.feature) {
        		return this.feature;
        	}
        	else{
        		try {
        			this.feature = $cookies.getObject( 'f' );
        		}
        		catch(e){
        			$cookies.remove( 'f' );
        		}

        		return this.feature;
        	}
        }

        function _getConfig(){
            if(this.config){
                return this.config;
            }
            else{

                try{
                    this.config = $cookies.getObject( 'c' );
                }
                catch(e)
                {
                    $cookies.remove( 'c' );
                }
                return this.config;
            }

        }

        function _getPlatform(){
            if (this.platform) {
                return this.platform;
            }else {
                try{
                    this.platform = $cookies.getObject( 'p' );
                } catch(e){
                    $cookies.remove('p');
                }
                return this.platform;
            }
        }

    }

})();
