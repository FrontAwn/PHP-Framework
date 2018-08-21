<?php 
namespace component\exception;

use component\compose\contract\ComposeComponentContract;
use component\exception\ExceptionExtension;

class ExceptionComponent implements ComposeComponentContract {

	private $configFileSuffix = ".php";

	public function getResponses(array $responses) {
		$setResponse = $responses['compose']['setResponse'];
		$setResponse('exception',array(
			'error' => $this->getErrorClosure(),
		));
	}

	public function getErrorClosure() {
		$self = $this;
		return function (string $message,$filename='common',$path=null) use ($self) {
			$self->error($message,$filename,$path);
		};		
	}

	public function error(string $message,$filename='common',$path=null) {
		if( is_null($path) ) $path = realpath(__DIR__."/../../config/exception");
		$path.='/';
		$configFile = $path.$filename.$this->configFileSuffix;
		$config = $this->getConfig($configFile);
		if( !empty($config) && isset($config["code"][$message]) ) {
			$code = $config['code'][$message];
			$msg = $config['message'][$code];
		} else {
			$msg = $message;
			$code = 400;
		}
		throw new ExceptionExtension($msg,$code);
	}

	private function getConfig($configFile) {
		return file_exists($configFile) ? require_once $configFile : [];
	}


}








