<?php 
namespace test;

class ObjectMegicFunction {

	public $dataList = []; 

	public function __get($key) {
		if ( isset($this->dataList[$key]) ) {
			return $this->dataList[$key];
		}
	}

	public function __set($key,$value) {
		$this->dataList[$key] = $value;
	}

	public function __call($functionName,$functionParams) {
		\show($functionName);
		\show($functionParams);
	}

	public static function __callStatic($functionName,$functionParams) {
		\show($functionName);
		\show($functionParams);
	}


}