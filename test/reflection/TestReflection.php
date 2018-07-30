<?php 
namespace test\reflection;

use ReflectionClass;

class TestReflection {

	private $reflectionInstance = null;

	public function __construct($class) {
		$this->initReflectionClassInstance($class);
	}

	public function initReflectionClassInstance($class) {
		$this->reflectionInstance = new ReflectionClass($class);
	}

	public function call($methodName,$parameters=null) {
		if( is_null($parameters) ) {
			return call_user_func(array($this->reflectionInstance, $methodName));	
		}else {
			return call_user_func(array($this->reflectionInstance, $methodName),$parameters);
		}
	}

}