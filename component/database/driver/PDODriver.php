<?php 
namespace component\database\driver;

use PDO;
use PDOExcption;
use component\container\ApplicationContainer;
use component\database\contract\DatabaseDriverContract;

class PDODriver implements DatabaseDriverContract{

	protected $db = null;

	private $dsn = "";

	private $username = null;

	private $password = null;

	private $options = [];

	private $commands = [];

	private $type = "mysql";

	private $configPath = "";

	private $configFileSuffix = ".php";

	private static $configs = [];

	private static $appContainer = null;

	private static $exception = null;

	public function __construct($databaseName,$configPath=null) {

		if( !isset(self::$configs[$databaseName]) ) {
			self::$appContainer = ApplicationContainer::getAppInstance();
			self::$exception = self::$appContainer->make('exceptionComponent');

			if ( !is_null($configPath) ) {
				$this->setConfigPath($configPath);
			} else {
				$this->configPath = realpath(__DIR__.'/../../../config/database')."/";
			}

			$configFile = $this->configPath.$databaseName.$this->configFileSuffix;

			if( !file_exists($configFile) ) {
				self::$exception->error("ERROR_MYSQL_NOT_FOUND_CONFIF_FILE",'mysql');
			}
			$config = require_once $configFile;
			self::$configs[$databaseName] = $config;
		}

		$this->connect(self::$configs[$databaseName]);
	}


	private function setConfigPath($path) {
		$this->configPath = $path;
	}

	public function connect(array $config = []) {

		if ( isset($config['type']) ) $this->type = strtolower($config['type']);
		if ( isset($config['username']) ) $this->username = $config['username'];
		if ( isset($config['password']) ) $this->password = $config['password'];
		if ( isset($config['options']) && is_array($config['options']) ) $this->options = $config['options'];
		if ( isset($config['commands']) && is_array($config['commands']) ) $this->commands = $config['commands'];

		if ( isset($config['dsn']) ) {
			$this->dsn = $config['dsn'];
		} else {
			$this->resolveDsn($config);
		}

		try {
			if ( is_null($this->db) ) {
				$this->db = new PDO($this->dsn,$this->username,$this->password,$this->options);
			}
			if( !empty($this->commands) ) {
				foreach ($this->commands as $key => $command) {
					$this->db->exec($command);
				}
			}
		} catch(PDOExcption $e) {
			self::$exception->error( $e->getMessage() );
		}


	}

	private function resolveDsn(array $config) {
		switch ($this->type) {
			case 'mysql':
				$this->dsn = $this->type.":";
				if( isset($config['socket']) ) {
					$this->dsn.= "unix_socket=".$config['socket'];
				} else {
					$this->dsn.= "host=".$config['host'].";";
				}
				$this->dsn.= "dbname=".$config['database'].";";
				if ( isset($config['port']) ) $this->dsn.= "port=".$config['port'].";";
				if ( isset($config['charset']) ) $this->dsn.= "charset=".$config['charset'].";";
				break;

			case 'sqlite':
				break;


			case 'oci':
				break;			
		}
	}


	public function getDatabase() {
		return ( !is_null($this->db) ) ? $this->db : null;
	}

}





































