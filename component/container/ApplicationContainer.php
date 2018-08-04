<?php 
namespace component\container;

use component\http\Http;
use component\compose\Compose;
use component\router\Router;
use component\exception\ExceptionComponent;
use component\database\Manager;

class ApplicationContainer extends Container {

	private static $appInstance = null;

	private $home = null;

	private $customPaths = [];

	public function __construct($homePath) {
		if( is_null(self::$appInstance) ) {
			self::$appInstance = $this;
		}

		$this->home = $homePath;
		$this->setDefaultPath($this->home);
		$this->bindDefaultComponent();
	}

	public static function getAppInstance() {
		return self::$appInstance;
	}

	protected function setDefaultPath($homePath) {
		$self = $this;
		$this->cache("DefaultPathProvider",function() use ($self) {
			return array(
				'home'=>$self->home,
				'app'=>$self->resolveDefaultPath('/app'),
				'component'=>$self->resolveDefaultPath('/component'),
				'public'=>$self->resolveDefaultPath('/public'),
				'static'=>$self->resolveDefaultPath('/static'),
				'vendor'=>$self->resolveDefaultPath('/vendor'),
			);
		});
	}

	protected function resolveDefaultPath($path) {
		return $this->home.$path;
	}

	protected function bindDefaultComponent() {
		$this->cache("exceptionComponent",ExceptionComponent::class);
		$this->cache("compose",Compose::class);
		$this->cache("http",Http::class);
		$this->cache("router",Router::class);
		$this->cache("databaseManager",Manager::class);
	}

	// public function setCustomPath($customKey,$customPath,$pathProviderKey='home') {
	// 	$self = $this;
	// 	$defaultPaths = $this->make("DefaultPathProvider");
	// 	if( isset($defaultPaths[$pathProviderKey]) ) {
	// 		$basePath = $defaultPaths[$pathProviderKey];
	// 	} else {
	// 		$basePath = $defaultPaths['home'];
	// 	}
	// 	$this->customPaths[$customKey] = $basePath.$customPath;
	// 	$this->cache("CustomPathProvider",function() use ($self) {
	// 		return $self->customPaths;
	// 	});
	// }

}













