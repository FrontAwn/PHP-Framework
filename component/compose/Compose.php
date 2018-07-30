<?php 
namespace component\compose;

use Closure;

class Compose {

	private $app = null;

	private $pipe = [];	

	private $responses = [];

	private $continue = true;

	public function __construct($application) {
		$this->app = $application;
		$this->init();
	}


	protected function init() {
		$this->setResponses('compose',[
			'exit'=>$this->notContinue(),
			'container'=>$this->getAppContainerClosure(),
			'setResponse'=>$this->getSetResponsesClosure(),
			'loadComponent'=>$this->getLoadComponentClosure(),
			'getLatestResponses'=>$this->getLatestResponsesClosure()
		]);

		$this->insertComponent("exceptionComponent");
		$this->insertComponent("http");
		$this->insertComponent("router");
	}


	public function start(){
		if( empty($this->pipe) ) {
			echo "没有服务";
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
		$method = function ($res) use ($self,$sign,$concrete,$parameters) {
			$component = $self->loadComponent($sign,$concrete,$parameters,$cache);
			$component->getResponses($res);
		};
		array_push($this->pipe,$method);
	}	



	protected function loadComponent($sign,$concrete=null,array $parameters=[],$cache=true) {
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


	protected function setResponses($key,$value) {
		$this->responses[$key] = $value;
	}

	protected function getLatestResponses() {
		return $this->responses;
	}

	protected function getLatestResponsesClosure() {
		$self = $this;
		return function () use ($self) {
			return $self->getLatestResponses();
		};
	}

	protected function getSetResponsesClosure() {
		$self = $this;
		return function ($key,$value) use ($self) {
			$self->setResponses($key,$value);
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
		$this->app->clear();
		$this->clear();
		echo json_encode(array(
			"code" => $code,
			"msg" => $message
		),JSON_UNESCAPED_UNICODE);
	}

}















