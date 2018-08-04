<?php 
namespace component\http;

use component\compose\contract\ComposeComponentContract;

class Http implements ComposeComponentContract {

	private $home;

	private $port = 80;

	private $url = "/";

	private $method = 'GET';

	private $header = [];

	private $queryString = null;

	private $cookie = "";

	private $code = 200;

	public function __construct() {
		$this->resolveRequest();
		$this->resolveCookie();
	}

	public function getResponses(array $responses) {
		$set = $responses['compose']['setResponse'];
		$set('http',array(
			"home" => $this->home,
			"port" => $this->port,
			"method" => $this->method,
			"url" => $this->url,
			"code"=>$this->code,
			"headers" => $this->header,
			"cookie" => $this->cookie,
			"setHeader" => $this->setHeader(),
			"setData" => $this->setData(),
		));
	}

	private function resolveRequest() {
		if(! is_null( $this->getServerOption('REQUEST_URI') ) ) {
			$this->url = explode("?", $this->getServerOption('REQUEST_URI'))[0];
		}
		$this->method = $this->getServerOption('REQUEST_METHOD','GET');
		$this->home = $this->getServerOption('SERVER_NAME','localhost');
		$this->port = $this->getServerOption('SERVER_PORT',80);
		$this->code = $this->getServerOption('REDIRECT_STATUS',200);
		$this->header = [
			'acceptEncoding'=>$this->getServerOption('HTTP_ACCEPT_ENCODING'),
			'acceptLanguage'=>$this->getServerOption('HTTP_ACCEPT_LANGUAGE'),
			'cacheControl'=>$this->getServerOption('HTTP_CACHE_CONTROL'),
			'connection'=>$this->getServerOption('HTTP_CONNECTION'),
			'runningTime'=>$this->getServerOption('REQUEST_TIME'),
			'clientInfo'=>$this->getServerOption('HTTP_USER_AGENT')
		];
	}

	private function resolveCookie() {
		if( !empty($_COOKIE) ) $this->cookie = $_COOKIE;
	}

	private function getServerOption($option,$default=null) {
		return isset($_SERVER[$option]) ? $_SERVER[$option] : $default;
	}

	private function setHeader() {
		return function ($headersDetail) {
			if( is_array($headersDetail) ) {
				foreach ($headersDetail as $title => $value) {
					header("{$title}: {$value}");
				}
			}

			if( is_string($headersDetail) ) {
				header($headersDetail);
			}
		};
	}

	private function setData() {
		return function (array $data,$code=200,$message='success') {
			$result = array(
				"code"=>$code,
				"msg"=>$message,
				"data"=>$data
			);
			echo json_encode($result,JSON_UNESCAPED_UNICODE);
		};
	}

}