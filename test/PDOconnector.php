<?php 
namespace test;

use PDO;
use PDOException;

class PDOconnector {

	private $dsn = "";

	private $host = "10.211.55.4";

	private $port = 3306;

	private $username = "root";

	private $password = "123456";

	private $database = "english";

	private $type = "mysql";

	private $options = [
		PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_AUTOCOMMIT => 0,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
	];

	private $connection = null;

	private $charset = 'utf8';

	public function __construct() {
		try {
			// $this->getConnection();
			$this->makeDsn();
			if ( is_null($this->connection) ) {
				$this->connection = new PDO($this->dsn,$this->username,$this->password,$this->options);
			}
		} catch(PDOException $e) {
			echo $e->getMessage();
		}
	}

	public function getConnection() {
		return $this->connection;
	}

	public function makeDsn() {
		$this->dsn = $this->type.":dbname=".$this->database.";host=".$this->host.";port=".$this->port.";charset=".$this->charset;
	}

}