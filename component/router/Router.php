<?php 
namespace component\router;

use Closure;
use component\compose\contract\ComposeComponentContract;

class Router implements ComposeComponentContract {

	private static $instance = null;

	private static $responses = null;

	private $url = "";

	private $method = "";

	private $routerMethod = "";

	private $defaultControllerNameSpace = "\app\controller";

	private $routes = [];

	private $group = [];

	private $groupPrefix = "";

	private $controllerObjects = [];

	public function getResponses(array $responses) {
		if( is_null(self::$instance) ) {
			self::$instance = $this;			
		}
		$this->url = $responses['http']['url'];
		$this->method = strtolower($responses['http']['method']);
		$parameters = [];
		$setResponse = $responses['compose']['setResponse'];
		switch ($this->method) {
			case 'get':
				$parameters = $_GET;
				break;
			case 'post':
				$parameters = $_POST;
				break;
		}

		$setResponse('router',[
			'params' => $parameters,
		]);

		self::$responses = $responses['compose']['getLatestResponses']();

	}


	private function setGroup(string $group,...$parameters) {
		$routeFilePath = $parameters[0];
		if ( $group[0] !== '/' ) {
			$group = '/'.$group;
		}
		$this->group[$group] = $routeFilePath;

		$this->resolveGroup($group);

		\debug($this->group,"group路由组列表");
	}

	private function resolveGroup(string $group) {
		$url = $this->url;
		$isExistsOffset = strpos($url,$group);

		if($isExistsOffset === 0) {
			$this->groupPrefix = $group;
			require_once $this->group[$group];
		}
	}

	private function setRoute(string $url, ...$parameters) {
		$error = self::$responses['exception']['error'];
		$lastIndex = count($parameters) - 1;
		$method = $parameters[$lastIndex];
		unset($parameters[$lastIndex]);

		if( empty($parameters) ) {
			$error('ERROR_ROUTER_PARAMETER_EXCEPTION');
		}

		$closure = [];
		$controller = [];
		$options = [];

		foreach ($parameters as $key => $param) {
			if ( $param instanceof Closure ) {
				array_push($closure,$param);
			} else if( is_string($param) && strpos($param,"@") !== false ) {
				array_push($controller,$param);
			} else if( is_array($param) ) {
				$options = array_merge($options,$param);
			} else {
				$error('ERROR_ROUTER_PARAMETER_EXCEPTION');
			}
		}
		
		$url = $this->resolveOptions('url',$url,$options);

		if( $this->url === $url && $this->method === $method ) {

			$this->routes[$url][$method] = [
				'closure' => $closure,
				'controller' => $controller,
			];
			$this->resolveRoute($url,$method);
			\debug($this->routes,"routes路由列表");
		}

	}
	


	private function resolveRoute($url,$method) {
		$responses = self::$responses;
		$getLatestResponses = $responses['compose']['getLatestResponses'];
		$routerStack = $this->routes[$url][$method];
		$closures = $routerStack['closure'];
		$controllers = $this->resolveController($routerStack['controller']);
		\debug($this->controllerObjects,"controller对象列表");
		//运行所有route设置的匿名函数
		if ( !empty($closures) ) {
			foreach ($closures as $closure) {
				$closure($getLatestResponses());
			}	
		}
		//调用所有的controler函数
		if( !empty($controllers) ) {
			foreach ($controllers as $key => $controller) {
				$controllerClass = $controller['class'];
				$controllerMethod = $controller['method'];
				$controllerClass->$controllerMethod($getLatestResponses());
			}
		}
		
	}



	private function resolveController(array $controllers) {
		if ( empty($controllers) ) return [];
		$error = self::$responses['exception']['error'];
		$resolvedControllers = [];
		foreach ($controllers as $key => $value) {
			$controllerOptions = explode("@", $value);

			
			$controllerMethod = end($controllerOptions);
			array_pop($controllerOptions);
			$controllerClassName = end($controllerOptions);
			array_pop($controllerOptions);

			if ( empty($controllerOptions) ) $controllerNameSpace = "";
			if( count($controllerOptions) > 1 ) {
				$controllerNameSpace = implode("\\", $controllerOptions);
				$controllerNameSpace.="\\";
			} else {
				$controllerNameSpace = $controllerOptions[0];
			}

			$controllerClass = $this->defaultControllerNameSpace."\\".$controllerNameSpace.$controllerClassName;

			if( isset($this->controllersObjects[$controllerClass]) ) {
				$controllerObject = $this->controllersObjects[$controllerClass];
			} else {
				$controllerObject = new $controllerClass;
				$this->controllerObjects[$controllerClass] = $controllerObject;
			}

			array_push($resolvedControllers, [
				'class' => $controllerObject,
				'method' => $controllerMethod
			]);

		}
		return $resolvedControllers;
	}

	private function resolveOptions(string $specify,$params,array $options) {
		switch ($specify) {
			case 'url':
				return $this->resolveUrl($params,$options);
				break;
		}
	}

	private function resolveUrl($url,array $options=[]) {
		if( $url[0] !== "/" ) {
			$url = "/".$url;
		}

		$prefix = isset($options['group']) ? $options['group'] : $this->groupPrefix;

		if( $prefix !== "" && $url === '/') $url = "";

		return $prefix.$url;
	}

	private function setControllerNameSpace($namespace) {
		$this->defaultControllerNameSpace = $namespace;
	}



	public static function __callStatic($methodName,$methodParams) {
		$self = self::$instance;
		$responses = self::$responses;
		$error = $responses['exception']['error'];
		$options = [
			"group" => "setGroup",
			"get" => "setRoute",
			"post" => "setRoute",
			"namespace" => "setControllerNameSpace",
		];
		if( isset($options[$methodName]) ) {
			array_push($methodParams,$methodName);
			call_user_func_array(array($self,$options[$methodName]), $methodParams);
		} else {
			$error('ERROR_ROUTER_METHOD_DENIED');
		}
	}

}