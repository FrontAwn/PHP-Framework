<?php 
namespace component\database;

use component\compose\contract\ComposeComponentContract;

class Manager implements ComposeComponentContract{

	private static $instance = null;

	private static $responses = null;

	private static $models = [];

	public function getResponses(array $responses) {
		if( is_null(self::$responses) ) self::$responses = $responses;
		if( is_null(self::$instance) ) self::$instance = $this;
		$setResponse = $responses['compose']['setResponse'];
		$setResponse('database',[
			'loadModel'=>$this->getLoadModelClosure(),
		]);
	}

	public function loadModel($databaseName,$tableName,$configPath=null,$namespace='\\app\\model\\',$model='Model') {
		$setResponse = self::$responses['compose']['setResponse'];
		$modifyResponse = self::$responses['compose']['modifyResponse'];
		//数据库配置文件名称
		$configName = ucfirst($databaseName);
		//要加载库的指定表名
		$tableClassName = ucfirst($tableName);
		$tableNamespace = $namespace.$databaseName;
		$model = $namespace.$model;

		$params = [
			'table'=>$tableClassName,
			'namespace'=>$tableNamespace,
			'path'=>$configPath
		];

		$databaseTableModel = call_user_func("{$model}::{$configName}",$params);
		$databases = call_user_func("{$model}::getDatabases");
		self::$models[$databaseName][$tableName] = $databaseTableModel;
		$setResponse('model',self::$models);
		$modifyResponse('database',['databaseList'=>$databases]);
	}

	public function getLoadModelClosure() {
		$self = $this;
		return function ($databaseName,$tableName,$configPath=null,$namespace='\\app\\model\\',$model='Model') use ($self) {
			$self->loadModel($databaseName,$tableName,$configPath,$namespace,$model);
		};
	}


}






