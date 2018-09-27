<?php 
namespace app\model;

use Closure;
use component\compose\Compose;
use component\database\driver\PDODriver;

class Model {

	protected $db = null;

	protected $table = null;

	private $values = []; 

	private $selectSql = "";

	private $fromSql = "";

	private $whereSql = "";

	private $childSql = "";

	private $limitSql = "";

	private $lastId = null;

	private $rowCount = null;

	private static $databases = [];

	private static $compose = null;

	private static $exception = null;

	public static function __callStatic($database,$params) {
		if( is_null(self::$compose) ) self::$compose = Compose::getComposeInstance();
		if( is_null(self::$exception) ) self::$exception = self::$compose->getLatestResponses()['exception'];
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
		$tableModelClass = $options['namespace'].'\\'.$options['table'];

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

		$tableModelObject = new $tableModelClass;
		call_user_func(array($tableModelObject,'setConnect'),$key);
		call_user_func(array($tableModelObject,'setTable'),lcfirst($options['table']));
		return $tableModelObject;
	}

	public static function getDatabases () {
		return self::$databases;
	}

	final protected function setConnect($key) {
		$this->db = self::$databases[$key];
	}

	final protected function setTable($tableName) {
		$this->table = $tableName;
	}



	protected function insert(array $datas,$table=null) {
		if ( empty($datas) ) return 0;
		$db = $this->db;

		if( is_null($table) ) $table = $this->table;

		$dataKeys = array_keys($datas);

		$columns = implode(",",$dataKeys);

		foreach ($dataKeys as &$key) {
			$key = ":".$key;
		}

		$values = implode(",",$dataKeys);

		$sql = "insert into {$table} ({$columns}) values ({$values})";

		$stmt = $db->prepare($sql);

		$stmt->execute($datas);

		$this->clearQueryCondition();

		return $db->lastInsertId();
	}


	protected function insertAll(array $datas,$table=null) {
		if ( empty($datas) ) return 0;
		$db = $this->db;
		if( is_null($table) ) $table = $this->table;
		$dataCount = count($datas);
		$dataKeys = array_keys($datas[0]);
		$dataKeysCount = count($dataKeys);
		$columns = implode(",",$dataKeys);

		$valuesSqlArray = [];
		for ($idx=1; $idx<=$dataCount; $idx++) {
			array_push($valuesSqlArray, $this->getInsertAllValuesSql($dataKeysCount));
		}

		$valuesSql = implode(",", $valuesSqlArray);

		$sql = "insert into {$table} ({$columns}) values {$valuesSql}";

		$values = [];

		foreach ($datas as $key => $data) {
			$values = array_merge($values,array_values($data));
		}
		
		// \debug($sql);

		$stmt = $db->prepare($sql);

		$stmt->execute($values);

		// \debug($values);

		// \debug($db->lastInsertId());

		if( $db->lastInsertId() == 0 ) {
			return [];
		} else {
			$ids = [];
			$saveId = ($db->lastInsertId())-1;
			for($idx=0; $idx<$dataCount; $idx++) {
				$saveId += 1;
				array_push($ids, $saveId);
			}
			return $ids;
		}


	}

	private function getInsertAllValuesSql(int $dataKeysCount) {
		$sql = "";
		for($idx=1;$idx<=$dataKeysCount;$idx++) {
			if($idx === $dataKeysCount) {
				$sql.= "?";
			}else {
				$sql.= "?,";
			}
		}
		$res = "({$sql})";
		return $res;
	}



	protected function update(array $datas,array $where,$table=null) {
		if ( empty($datas) ) return 0;

		if( is_null($table) ) $table = $this->table;
		
		$this->where($where);		

		$whereSql = $this->whereSql;
		$whereValues = $this->values;

		$this->clearQueryCondition();

		$conditions = [];

		foreach ($datas as $key => $value) {
			$conditions[] = "{$key}=:{$key}";
		}

		$conditions = implode(",", $conditions);

		$values = array_merge($datas,$whereValues);

		$sql = "update {$table} set {$conditions} {$whereSql}";

		// \debug($sql,"update语句");
		// \debug($values,"update所有绑定值");

		$statement = $this->db->prepare($sql);
		$statement->execute($values);

		return $statement->rowCount();
	}

	protected function delete(array $where,$table=null) {
		$error = self::$exception['error'];
		if ( empty($where) ) $error("请添加删除条件(where)");

		if( is_null($table) ) $table = $this->table;
		
		$this->where($where);		

		$whereSql = $this->whereSql;
		$whereValues = $this->values;

		$this->clearQueryCondition();

		$sql = "delete from {$table} {$whereSql}";
		// \debug($sql,"delete删除语句");
		// \debug($whereValues,"delete所有绑定值");
		$statement = $this->db->prepare($sql);
		$statement->execute($whereValues);
		return $statement->rowCount();
	}


	protected function deleteById($id,$table=null) {
		$condition = [$this->equals('id',$id)];
		return $this->delete($condition,$table);
	}


	protected function sqlQuery(string $sql,array $values,$mode="all") {
		$db = $this->db;
		$statement = $db->prepare($sql);
		$statement->execute($values);
		if($mode == "all") $result = $statement->fetchAll();
		if($mode == "once") $result = $statement->fetch();
		return $result;
	}

	protected function sqlExecute(string $sql,array $values) {
		$db = $this->db;
		$statement = $db->prepare($sql);
		$statement->execute($values);
		$this->lastId = $db->lastInsertId();
		$this->rowCount = $statement->rowCount();
	}


	protected function equals($column,$value,$conj="and") {
		$pre = "{$column}=:{$column}";
		$res = [$column=>$value];

		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function notEquals($column,$value,$conj="and") {
		$pre = "{$column}!=:{$column}";
		$res = [$column=>$value];

		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}


	protected function greaterThan($column,$value,$conj="and") {
		$pre = "{$column}>:{$column}";
		$res = [$column=>$value];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function lessThan($column,$value,$conj="and") {
		$pre = "{$column}<:{$column}";
		$res = [$column=>$value];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function between($column,array $values,$conj="and") {
		$large = $column.'Large';
		$small = $column.'Small';
		$pre = "{$column} between :{$large} and :{$small}";
		$res = [$large=>$values['large'],$small=>$values['small']];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function like($column,$value,$conj="and") {
		$pre = "{$column} like :{$column}";
		$res = [$column=>$value];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function isNull($column,$conj="and") {
		$pre = "{$column} is null";
		$res = [];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function in($column,array $values,$conj="and") {
		foreach ($values as $key => &$value) {
			$value = "'".$value."'";
		}
		$values = implode(",", $values);
		$pre = "{$column} in ({$values})";
		$res = [];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function notIn($column,array $values,$conj="and") {
		foreach ($values as $key => &$value) {
			$value = "'".$value."'";
		}
		$values = implode(",", $values);
		$pre = "{$column} not in ({$values})";
		$res = [];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}

	protected function isNotNull($column,$conj="and") {
		$pre = "{$column} is not null";
		$res = [];
		return [
			"pre"=>$pre,
			"res"=>$res,
			"conj"=>$conj,
		];
	}


	protected function query(array $options=[],string $mode="all") {
		if ( empty($options) ) {
			return $this->select()->from()->all();
		} else {

			if( isset($options["column"]) ) {
				$this->select($options["column"]);
			} else {
				$this->select();
			}

			if( isset($options["table"]) ) {
				$this->from($options["table"]);
			} else {
				$this->from();
			}

			if ( isset($options["where"]) ) {
				$this->where($options["where"]);
			}

			if( isset($options["child"]) ) {
				$this->child($options["child"]);
			}

			if( isset($options['number']) ) {
				$this->number($options['number']);
			}

			if( isset($options['page']) || isset($options['length']) ) {

				$page = isset($options['page']) ? $options['page'] : 1;

				$length = isset($options['length']) ? $options['length'] : 9999;

				$this->limit([
					"page"=>$page,
					"length"=>$length,
				]);
			}

			switch ($mode) {
				case 'all':
					return $this->all();
					break;
				case 'once':
					return $this->once();
					break;
			}

		}

	}

	protected function select(array $columns=["*"]) {
		if( count($columns) > 1 ) {
			$columnSql = implode(",", $columns);
		} else {
			$columnSql = $columns[0];
		}

		$sql = "select {$columnSql} ";
		$this->selectSql = $sql;
		return $this;
	}

	protected function from($table=null) {
		if( is_null($table) ) $tableSql = $this->table;
		if( is_string($table) ) $tableSql = $table;
		if( is_array($table) ) $tableSql = implode(",", $table);
		$sql = "from {$tableSql} ";
		$this->fromSql = $sql;
		return $this;
	}

	protected function where(array $options) {
		$whereSql = "where ";
		$whereValues = [];
		$length = count($options);
		foreach ($options as $key => $option) {
			if( $length === 1 || $key === ($length-1) ) {
				$whereSql.= $option["pre"]." ";
			} else {
				$whereSql.= $option["pre"]." {$option["conj"]} ";
			}
			$whereValues = array_merge($whereValues,$option["res"]);
		}
		$this->whereSql = $whereSql;
		$this->values = $whereValues;

		return $this;
	}

	protected function child(string $childSql) {
		$this->childSql = "{$childSql} ";
		return $this;
	} 

	protected function number(int $num) {
		$this->limitSql = " limit {$num}";
		return $this;
	}

	protected function limit(array $limit=[]) {
		if( !isset($limit["page"]) ) {
			$page = 1;
		} else {
			$page = $limit["page"];
		}

		if( !isset($limit["length"]) ) {
			$length = 10000;
		} else {
			$length = $limit["length"];
		}

		$limitPage = ($page-1)*$length;
		$limitLength = $length;

		$this->limitSql = " limit {$limitPage},{$limitLength}";
		return $this;
	}


	private function getQueryResult() {
		$error = self::$exception['error'];
		if( !$this->isQuery() ) $error("搜索条件不够，请确认是否调用select和where函数");
		$db = $this->db;
		$sql = $this->selectSql.$this->fromSql.$this->whereSql.$this->childSql.$this->limitSql;
		// \debug($this->selectSql,'select语句');
		// \debug($this->childSql,'child语句');
		// \debug($this->whereSql,"当前sql语句where子句");
		// \debug($this->values,"当前sql语句where子句所有绑定值");
		\debug($sql,"当前sql语句");
		$statement = $db->prepare($sql);
		$statement->execute($this->values);
		return $statement;
	}

	protected function once() {
		$statement = $this->getQueryResult();
		$result = $statement->fetch();
		if( $result === false) {
			$result = [];
		}
		$this->clearQueryCondition();
		return $result;
	}

	protected function all() {
		$statement = $this->getQueryResult();
		$result = $statement->fetchAll();
		$this->clearQueryCondition();
		return $result;
	}

	private function isQuery() {
		return is_null($this->selectSql) ? false : true;
	}

	private function clearQueryCondition() {
		$this->selectSql = "";
		$this->whereSql = "";
		$this->childSql = "";
		$this->limitSql = "";
		$this->values = [];
	}

	protected function getLastId() {
		return $this->lastId;
	}

	protected function getRowCount() {
		return $this->rowCount;
	}


// ---------------------------------commonFunction---------------------------

	protected function indexBy($key,array $datas) {
		$res = [];

		foreach ($datas as $value) {
			$res[$value[$key]] = $value;
		}

		return $res; 
	}

	protected function randomOfNumber($max,$length=20,array $except=[],$min=1) {
		$scopeArray = range($min, $max);
		$diff = array_diff($scopeArray, $except);
		if( !empty($diff) ) {
			shuffle($diff);
			if( $length !== 0) {
				$random = array_slice($diff, 0, $length);
			} else {
				$random = $diff;
			}
			return $random;
		} else {
			return [];
		}
	}

	protected function randomOfArray(array $datas=[],$length=20,array $except=[]){
		shuffle($datas);

		if ( !empty($except) ) {
			$res = array_diff($datas, $except);
		} else {
			$res = $datas;
		}

		if ( $length !== 0 ) {
			return array_slice($res, 0, $length);	
		} else {
			return $res;
		}
		
	}

}






