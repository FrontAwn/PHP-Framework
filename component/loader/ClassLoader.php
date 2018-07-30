<?php 
namespace component\loader;

use ReflectionClass;

class ClassLoader  {

	private static $instance = null;

	private function __construct() {}

	private function __clone() {}

	public function getInstance() {
		if ( is_null(self::$instance) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function register() {
		spl_autoload_register( array($this,'loader'),true );
	}

	private function loader($className) {
		$classFile = $this->getClassFileInfo($className);
		if ( $this->hasClassFile($classFile) ) {
			require_once $classFile;
		} else {
			// throw new \Exception("<div style='color:#E9935F'>{$classFile}文件不存在!!!</div>", 1);
			echo "<div style='color:#E9935F'>{$classFile} 文件不存在!</div>";
		}
	}

	private function getClassFileInfo($className):string {

		$nameSpaceList = (new ReflectionClass($this))->getNamespaceName();

		$nameSpace = implode('/',explode('\\',$nameSpaceList));

		$homePath = str_replace($nameSpace, "", __DIR__);

		$pathList = explode('\\',$className);

		$pathLength = count($pathList);

		$fileExstension = ".php";

		$fileName = $pathList[$pathLength-1].$fileExstension;

		unset($pathList[$pathLength-1]);

		$dirName = $homePath.(implode("/",$pathList));

		return $dirName."/".$fileName;
	}

	private function hasClassFile($classFilePath) {
		return file_exists($classFilePath);
	}

}