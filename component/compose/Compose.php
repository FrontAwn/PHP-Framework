<?php 
namespace component\compose;

use Closure;

class Compose {

	private $app = null;

	private $pipe = [];	

	private $responses = [];

	private $continue = true;

	private static $composeInstance = null;

	public function __construct($application) {
		if( is_null($this->app) ) $this->app = $application;
		if( is_null(self::$composeInstance) ) self::$composeInstance = $this;
		$this->init();
	}

	public function getComposeInstance() {
		return self::$composeInstance;
	}


	protected function init() {
		$this->setResponse('compose',[
			'exit'=>$this->notContinue(),
			'container'=>$this->getAppContainerClosure(),
			'loadComponent'=>$this->getLoadComponentClosure(),
			'setResponse'=>$this->getSetResponseClosure(),
			'modifyResponse'=>$this->getModifyResponseClosure(),
			'getLatestResponses'=>$this->getLatestResponsesClosure()
		]);

		$this->setResponse('path',$this->app->make('DefaultPathProvider'));

		$this->insertComponent("exceptionComponent");
		$this->insertComponent("http");
		$this->insertComponent("databaseManager");
		$this->insertComponent("router");
	}


	public function start(){
		if( empty($this->pipe) ) {
			$this->end();
			return;
		}

		$length = $this->getPipeLength();
		for( $idx=0; $idx<=$length-1; $idx++) {
			if($this->continue) {
				call_user_func($this->pipe[$idx],$this->responses);
				continue;
			}
			return;
		}
		\debug( array_keys($this->responses) ,"responses参数列表");
	}


	public function insertClosure(Closure $method) {
		array_push($this->pipe, $method);
	}


	public function insertComponent($sign,$concrete=null,array $parameters=[],$cache=true) {
		$self = $this;
		$method = function ($res) use ($self,$sign,$concrete,$parameters,$cache) {
			$component = $self->loadComponent($sign,$concrete,$parameters,$cache);
			$component->getResponses($res);
		};
		array_push($this->pipe,$method);
	}	



	public function loadComponent($sign,$concrete=null,array $parameters=[],$cache=true) {
		if( !$this->app->hasBinding($sign) ) {
			if( $cache ) {
				$this->app->cache($sign,$concrete);
			}else{
				$this->app->bind($sign,$concrete);
			}
		}
		$component = $this->app->make($sign,$parameters);
		return $component;
	}


	protected function notContinue() {
		$self = $this;
		return function () use ($self) {
			$self->continue = false;
		};
	}


	protected function setResponse($key,$value) {
		$this->responses[$key] = $value;
	}

	protected function modifyResponse($key,array $params) {
		if ( !isset($this->responses[$key]) ) return false;

		foreach ($params as $option => $value) {
			if(is_int($key)) {
				continue;
			}
			$this->responses[$key][$option] = $value;
		}
	}

	public function getLatestResponses() {
		return $this->responses;
	}

	protected function getLatestResponsesClosure() {
		$self = $this;
		return function () use ($self) {
			return $self->getLatestResponses();
		};
	}

	protected function getSetResponseClosure() {
		$self = $this;
		return function ($key,$value) use ($self) {
			$self->setResponse($key,$value);
		};
	}

	protected function getModifyResponseClosure() {
		$self = $this;
		return function($key,array $params) use ($self) {
			$self->modifyResponse($key,$params);
		};
	}

	protected function getAppContainerClosure() {
		$self = $this;
		return function() use ($self) {
			return $self->app;
		};
	}

	protected function getLoadComponentClosure() {
		$self = $this;
		return function($sign,$concrete=null,array $parameters=[],$cache=true) use ($self) {
			return $self->loadComponent($sign,$concrete,$parameters,$cache);
		};
	}

	protected function getPipeLength() {
		return count($this->pipe);
	}

	protected function clear() {
		$this->continue = false;
		$this->app = null;
		$this->pipe = [];
		$this->responses = [];
	}

	public function end($exception){
		$message = $exception->getMessage();
		$code = $exception->getCode();
		if( isset($this->responses['database']['databaseList']) && count($this->responses['database']['databaseList']) > 0) {
			$databaseList = $this->responses['database']['databaseList'];
			foreach ($databaseList as $name => $database) {
				$database->rollBack();
			}
		}
		$this->app->clear();
		$this->clear();
		echo json_encode(array(
			"code" => $code,
			"msg" => $message
		),JSON_UNESCAPED_UNICODE);
	}

}















