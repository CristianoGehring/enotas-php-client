<?php
	namespace eNotasGW\Api; 
	
	require('Helper.php');
	
	require('Exceptions/requestException.php');
	require('Exceptions/apiException.php');
	require('Exceptions/invalidApiKeyException.php');
	require('Exceptions/unauthorizedException.php');

	require('ApiBase.php');
	require('nfeApi.php');
	require('empresaApi.php');
	require('prefeituraApi.php');
	require('servicosMunicipaisApi.php');

	require('fileParameter.php');
	require('request.php');
	require('response.php');

	require('Proxy/proxyBase.php');
	require('Proxy/curlProxy.php');

	require('Media/Formatters/formatterBase.php');
	require('Media/Formatters/jsonFormatter.php');
	require('Media/Formatters/formDataFormatter.php');

	use eNotasGW\Api as api;
	use eNotasGW\Api\Proxy as proxy;
	use eNotasGW\Api\Media\Formatters as formatters;  

	class eNotasGW {
		private static $_apiKey;
		private static $_defaultContentType = 'application/json';
		private static $_baseUrl = 'http://api.enotasgw.com.br';
		private static $_version = '1';
		private static $_versionedBaseUrl;
		private static $_proxy;
		private static $_formmaters;
		private static $_trustedCAListPath;

		public static $EmpresaApi;
		public static $NFeApi;
		public static $PrefeituraApi;
		public static $ServicosMunicipaisApi;

		public static function configure($config) {
			$config = (object)$config;

			self::$_formmaters = array(
				'application/json' => new formatters\jsonFormatter(),
				'multipart/form-data' => new formatters\formDataFormatter()
			);

			if(!isset($config->apiKey)) {
				throw new \Exception('A api key deve ser definida no método configure.');
			}

			self::$_apiKey = $config->apiKey;

			if(isset($config->baseUrl)) {
				self::$_baseUrl = $config->baseUrl;
			}

			if(isset($config->version)) {
				self::$_version = $config->version;
			}

			if(isset($config->defaultContentType)) {
				self::$_defaultContentType = $config->defaultContentType;
			}
			
			if(isset($config->_trustedCAListPath)) {
				self::$_trustedCAListPath = $config->_trustedCAListPath;
			}
			else {
				self::$_trustedCAListPath = dirname(__FILE__) . '/Files/ca-bundle.crt';
			}

			self::$_versionedBaseUrl = self::$_baseUrl . '/v' . self::$_version;

			self::init();
		}

		public static function getMediaFormatter($contentType) {
			$contentType = explode(';', $contentType);
			
			return self::$_formmaters[$contentType[0]];
		}

		private static function init() {
			self::$_proxy = self::createProxy();
			self::$NFeApi = new api\nfeApi(self::$_proxy);
			self::$EmpresaApi = new api\empresaApi(self::$_proxy);
			self::$PrefeituraApi = new api\prefeituraApi(self::$_proxy);
			self::$ServicosMunicipaisApi = new api\servicosMunicipaisApi(self::$_proxy);
		}

		private static function createProxy() {
			return new proxy\curlProxy(array(
			  'baseUrl' => self::$_versionedBaseUrl,
			  'apiKey' => self::$_apiKey,
			  'defaultContentType' => self::$_defaultContentType,
			  'trustedCAListPath' => self::$_trustedCAListPath
			));
		}
	}
?>
