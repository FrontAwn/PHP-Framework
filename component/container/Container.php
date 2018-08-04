<?php 
namespace component\container;

use Closure;
use ArrayAccess;
use ReflectionClass;
use ReflectionParameter;
use component\container\contract\ContainerContract;

class Container implements ContainerContract{

	//缓存被解析过的实例
	private $instances = [];
	//存储bind的所有函数脚本
	private $bindings = [];

	private $parameters = [];

	private $resolved = [];

	private $buildStack = [];

	public function bind($sign,$concrete=null,$cache=false) {

		$this->removeRelateBuild($sign);

		$concrete = is_null($concrete) ? $sign : $concrete;

		$concrete = (! $concrete instanceof Closure ) ? 
				$this->generateScript($sign,$concrete) : 
				$this->recastScript($sign,$concrete);

		$this->bindings[$sign] = compact('concrete','cache'); 

	}


	/**
	*	Bind函数被调用时如果第二个参数不是匿名函数则被调用生成函数脚本
	**/
	protected function generateScript($sign,$concrete) {
		return function ($container,$parameters=[]) use ($sign,$concrete) {
			if( $sign == $concrete ) {
				return $container->build($concrete,$sign);
			}
			return $container->make($concrete,$parameters);
		};
	}


	/**
	*	Bind函数被调用时如果第二个参数是自定义函数就会被包装
	**/
	protected function recastScript($sign,$concrete) {
		return function ($container,$parameters=[]) use ($concrete) {
			return call_user_func_array($concrete, $parameters);
		};
	}



	public function make($sign,array $parameters=[]) {

		if( $this->instances[$sign] ) return $this->instances[$sign];

		$this->parameters[] = $parameters;
		// \debug($this->parameters,"make函数: 函数调用时所有的参数堆栈");

		$concrete = $this->getScript($sign);

		$object = $this->build($concrete,$sign);

		if( $this->isCache($sign) ) $this->instances[$sign] = $object;

		$this->resolved[$sign] = true;

		array_pop($this->parameters);
		// \debug($this->parameters,"make函数: 函数运行结束后参数堆栈");
		return $object;

	}
 


	protected function build($concrete,$sign) {
		$this->recordBuildStack($sign,$concrete,$this->getLastParameter);
		if( $concrete instanceof Closure ) {
			return $this->buildInClosureFunction($concrete,$sign);
		} else {
			return $this->buildInReflectionClass($concrete,$sign);
		}
	}


	/**
	*	Build函数的详细处理
	**/

	protected function buildInReflectionClass($className) {
		$reflection = new ReflectionClass($className);

		$constructor = $reflection->getConstructor();

		if( is_null($constructor) || count($constructor->getParameters()) === 0 ) {
			return new $className;
		}


		$constructParameters = $constructor->getParameters();

		$resolvedParameters = $this->resolveConstructParameters($constructParameters);

		return $reflection->newInstanceArgs($resolvedParameters);
	}

	protected function buildInClosureFunction(Closure $method,$sign) {
		return $method($this,$this->getLastParameter());
	}


	/**
	*	ReflectionMethod构造函数的参数处理
	**/

	protected function resolveConstructParameters(array $parameters) {
		$resolvedParameters = [];
		foreach ($parameters as $param) {

			if(! is_null($param->getClass()) ) {
				$resolvedParameters[] = $this->resoveClassParameter($param);
				continue;
			}

			if( $this->hasParameterOverride($param) ) {
				$resolvedParameters[] = $this->getParameterOverride($param);
			} else {
				$resolvedParameters[] = $this->getParameterDefault($param);
			}

		}
		return $resolvedParameters;
	}

	protected function hasParameterOverride(ReflectionParameter $param) {
		return array_key_exists(
            $param->name, $this->getLastParameter()
        );
	}

	protected function getParameterOverride(ReflectionParameter $param) {
		return $this->getLastParameter()[$param->name];
	}

	protected function getParameterDefault(ReflectionParameter $param) {
		if( $param->isDefaultValueAvailable() ) {
			return $param->getDefaultValue();
		}
		//！！！！！！！！！！！！！！！！！！！未完成
		$paramVariableName = $param->getName();
		echo "<br/>构造函数参数变量名{$paramVariableName}没有传入，且没有默认值";
	}


	/**
	*	处理ReflectionParameter有预定义类的类型参数
	**/
	protected function resoveClassParameter(ReflectionParameter $param) {
		$paramName = $param->name;
		$className = $param->getClass()->name;
		$paramNameObject = $this->resoveClassParameterByBinding($paramName,$className);

		return ( is_null($paramNameObject) ) ? 
					$this->resoveClassParameterByCommon($param) :
					$paramNameObject;

	}

	
	protected function resoveClassParameterByBinding($paramName,$className) {
		if($this->hasBinding($paramName)) {
			$paramNameObject = $this->make($paramName);
			if( $paramNameObject instanceof $className) {
				return $paramNameObject;
			}
		}
		
		return null;
	}


	protected function resoveClassParameterByCommon(ReflectionParameter $param) {
		$className = $param->getClass()->name;
		$parameters = $this->hasParameterOverride($param) ?
					  $this->getParameterOverride($param) : null;
		if( is_null($parameters) ) {
			return $this->make($className);
		} else {
			return $this->make($className,$parameters);
		}
	}


	protected function recordBuildStack($sign,$concrete,$parameters=[]) {
		$concrete = (! $concrete instanceof Closure) ? $concrete : 'ScriptFunction';
		$parameters = empty($parameters) ? "empty" : $parameters;
		$stacks = [
			"concrete" => $concrete,
			"params" => $parameters,
		]; 
		$this->buildStack[$sign] = $stacks;
	}

	/**
	*	make函数调用中，用来调用对应sign存储的脚本
	**/
	protected function getScript($sign) {
		if( $this->hasBinding($sign) ) {
			return $this->bindings[$sign]['concrete'];
		}
		return $sign;
	}

	protected function getLastParameter() {
		return count($this->parameters) ? end($this->parameters) : [];
	}

	public function cache($sign,$concrete=null,$cache=true) {
		$this->bind($sign,$concrete,$cache);
	}


	public function hasBinding($sign) {
		return isset( $this->bindings[$sign] );
	}

	protected function hasInstance($sign) {
		return isset( $this->instances[$sign] );
	}

	protected function isResolved($sign) {
		return $this->resolved[$sign];
	}

	protected function isCache($sign) {
		return $this->bindings[$sign]['cache'];
	}

	public function clear(){
		$this->resolved = [];
		$this->instances = [];
		$this->bindings = [];
	}

	protected function removeRelateBuild($sign) {
		if( $this->hasBinding($sign) ) unset($this->bindings[$sign]);
		if( $this->hasInstance($sign) ) unset($this->instances[$sign]);
		if( $this->isResolved($sign) ) unset($this->resolved[$sign]);
	}

	protected function removeBinding($sign) {
		if( $this->hasBinding($sign) ) {
			unset($this->bindings[$sign]);
			return true;
		}else{
			return false;
		}
	}

	protected function removeBindingAll() {
		$this->bindings = [];
		return true;
	}

	protected function removeInstance($sign){
		if ( !$this->hasInstance($sign) ) {
			return false;
		}

		unset( $this->$instances[$sign] );
		return true;
	}

	protected function removeInstanceAll() {
		$this->instances = [];
		return true;
	}

	public function getBindings() {
		return $this->bindings;
	}

	public function getCacheInstances() {
		return $this->instances;
	}

	public function getBuildStack() {
		return $this->buildStack;
	}

	// debug 函数
	public function showBuildStack() {
		\debug($this->buildStack,"BuildStack");
	}

	public function showBindings() {
		\debug($this->bindings);
	}

	public function showCache() {
		\debug($this->instances);
	}

}











