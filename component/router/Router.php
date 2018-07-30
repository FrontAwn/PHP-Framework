<?php 
namespace component\router;

use Closure;
use component\compose\contract\ComposeComponentContract;

class Router implements ComposeComponentContract {

	private static $instance = null;

	private static $responses = null;

	private $url = "";

	private $method = "";

	private $namespace = "\app\controller";

	private $routes = [];

	private $group = [];

	private $groupPrefix = "";

	private $groupPrefixs = [];

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

	public function match ($sign,$type='route') {	
		if( $type === 'route' ) {
			if( $this->compareRoute($sign) && $this->hasRouteMethod($sign,$this->method) ) {
				$this->resolveRoute($sign);
			} else {
				$this->handleRouterException();
			}
		}

		if( $type === 'group' ) {
			$this->resolveGroup($sign);
		}

	}

	public function getMatchClosure() {
		$self = $this;
		return function ($sign,$type='route') use ($self) {
			$self->match($sign,$type);
		};
	}

	public function redirect($url,$method="get") {
		$this->url = $url;
		$this->method = $method;
		$this->match($url);
	}

	private function setGroup(string $group,...$parameters) {
		$routeFilePath = $parameters[0];
		if ( $group[0] !== '/' ) {
			$group = '/'.$group;
		}
		$this->group[$group] = $routeFilePath;

		//从这里才开始匹配所设置的group,如果匹配成功则引入group文件
		$this->match($group,'group');
		\debug($this->group,"group路由组列表");
	}

	private function setRoute(string $url, ...$parameters) {
		$error = self::$responses['exception']['error'];
		$lastIndex = count($parameters) - 1;
		$method = $parameters[$lastIndex];
		unset($parameters[$lastIndex]);
		$length = count($parameters);
		$closure = [];
		$controller = [];
		$options = [];
		if( $length === 0 ) {
			$error('ERROR_ROUTER_PARAMETER_EXCEPTION');
		}

		for ($idx=0; $idx<$length; $idx++) {
			if ( $parameters[$idx] instanceof Closure ) {
				array_push($closure,$parameters[$idx]);
			} else if( is_string($parameters[$idx]) && strpos($parameters[$idx],"@") !== false ) {
				array_push($controller,$parameters[$idx]);
			} else if( is_array($parameters[$idx]) ) {
				$options = array_merge($options,$parameters[$idx]);
			} else {
				$error('ERROR_ROUTER_PARAMETER_EXCEPTION');
			}
		}

		$url = $this->resolveUrl($url,$options);

		$this->routes[$url][$method] = [
			'closure' => $closure,
			'controller' => $controller,
		];

		\debug($this->routes,"routes路由列表");


		//从这里才开始匹配所设置的routes,如果匹配成功则调用对应route
		$this->match($url,'route');

	}
	

	private function resolveGroup(string $group) {
		$url = $this->url;
		$isExistsOffset = strpos($url,$group);

		if($isExistsOffset === 0) {
			$this->groupPrefix = $group;
			require_once $this->group[$group];
		}
	}



	private function resolveRoute($url=null) {
		$responses = self::$responses;
		$route = ( is_null($url) ) ? $this->url : $url;
		$method = $this->method;
		$routerStack = $this->routes[$route][$method];
		$closures = $routerStack['closure'];
		$controllers = $this->resolveController($routerStack['controller']);
		\debug($this->controllerObjects,"controller对象列表");
		//运行所有route设置的匿名函数
		if ( !empty($closures) ) {
			foreach ($closures as $closure) {
				$closure($responses);
			}	
		}
		//调用所有的controler函数
		if( !empty($controllers) ) {
			foreach ($controllers as $key => $controller) {
				$controllerClass = $controller['class'];
				$controllerMethod = $controller['method'];
				$controllerClass->$controllerMethod($responses);
			}
		}
		
	}



	private function resolveController(array $controllers) {
		if ( empty($controllers) ) return [];
		$resolvedControllers = [];
		foreach ($controllers as $key => $value) {
			$controllerOptions = explode("@", $value);
			$controllerClass = $this->namespace."\\".$controllerOptions[0];
			$controllerMethod = $controllerOptions[1];
			
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



	private function resolveUrl($url,array $options=[]) {
		if( $url[0] !== "/" ) {
			$url = "/".$url;
		}

		$prefix = isset($options['group']) ? $options['group'] : $this->groupPrefix;

		if( $prefix !== "" && $url === '/') $url = "";

		// if( isset($options['redirect']) ) $this->url = $options['redirect'];

		return $prefix.$url;
	}



	private function compareRoute(string $url) {
		return ($url === $this->url) ? true : false;
	}

	private function hasGroup(string $group) {
		return isset($this->group[$group]);
	}

	private function hasRoute(string $route) {
		return isset( $this->routes[$route] );
	}

	private function hasRouteMethod(string $route,string $method) {
		return isset( $this->routes[$route][$method] );
	}



	public static function __callStatic($methodName,$methodParams) {
		$self = self::$instance;
		$responses = self::$responses;
		$error = $responses['exception']['error'];
		$options = [
			"group" => "setGroup",
			"get" => "setRoute",
			"post" => "setRoute",
		];
		if( isset($options[$methodName]) ) {
			array_push($methodParams,$methodName);
			call_user_func_array(array($self,$options[$methodName]), $methodParams);
		} else {
			$error('ERROR_ROUTER_METHOD_DENIED');
		}
	}

	private function handleRouterException() {
		$error = self::$responses['exception']['error'];
		$error('ERROR_ROUTER_NOT_MATCH');
		// require_once realpath(__DIR__.'/../../app/Error.php');
	}

}