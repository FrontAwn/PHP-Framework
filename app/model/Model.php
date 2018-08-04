<?php 
namespace app\model;

use component\database\driver\PDODriver;
use component\compose\Compose;


class Model {

	protected $db = null;

	protected $table = null;

	private static $databases = [];

	private static $compose = null;

	public static function __callStatic($database,$params) {
		if( is_null(self::$compose) ) self::$compose = Compose::getComposeInstance();
		$options = $params[0];
		//self::$databases所缓存的键名
		$key = strtolower($database);
		//数据库配置文件名
		$databaseConfig = $database;
		//数据库配置文件存放的路径
		$databaseConfigPath = $options['path'];
		//存在ioc容器中的键名
		$databaseContainerKey = "database".$database;
		//要加载数据库中的对应表的完整类名
		$tableClass = $options['namespace'].'\\'.$options['table'];

		//使用PDO驱动加载对应配置的数据库对象
		if( !isset(self::$databases[$key]) ) {
			$driver = self::$compose->loadComponent($databaseContainerKey,PDODriver::class,[
				"databaseName"=>$databaseConfig,
				"configPath"=>$databaseConfigPath
			]);
			$db = $driver->getDatabase();
			if( !$db->inTransaction() ) {
				$db->beginTransaction();
			}
			self::$databases[$key] = $db;
		}

		$tableObject = new $tableClass;
		call_user_func(array($tableObject,'getConnect'),$key);
		call_user_func(array($tableObject,'setTable'),strtolower($options['table']));
		return $tableObject;
	}

	public static function getDatabases () {
		return self::$databases;
	}

	final protected function getConnect($key) {
		$this->db = self::$databases[$key];
	}

	final protected function setTable($tableName) {
		$this->table = $tableName;
	}
















}