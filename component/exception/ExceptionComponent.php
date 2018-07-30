<?php 
namespace component\exception;

use component\compose\contract\ComposeComponentContract;
use component\exception\ExceptionExtension;

class ExceptionComponent implements ComposeComponentContract {

	private $configPath = __DIR__."/config/";

	private $configFileSuffix = ".php";

	private $config = [];

	public function getResponses(array $responses) {
		$setResponse = $responses['compose']['setResponse'];
		$setResponse('exception',array(
			'error' => $this->error(),
		));
	}

	public function error() {
		$self = $this;
		return function (string $message,string $filename='common') use ($self) {
			$file = $self->getConfigFile($filename);
			$self->getConfig($filename);
			$config = $self->config;
			if( !empty($config) ) {
				$code = $config['code'][$message];
				$msg = $config['message'][$code];
			} else {
				$msg = $message;
				$code = 400;
			}
			throw new ExceptionExtension($msg,$code);
		};		
	}


	private function configExists($file) {
		return file_exists($file);
	}

	private function configRequire($file) {
		return require_once $file;
	}

	private function getConfigFile($filename) {
		return $this->configPath.$filename.$this->configFileSuffix;
	}

	private function getConfig($filename) {
		$file = $this->getConfigFile($filename);
		$this->config = $this->configExists($file) ? $this->configRequire($file) : [];
	}

}








