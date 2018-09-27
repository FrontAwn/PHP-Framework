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

		// 数据库配置文件名称
		$configName = ucfirst($databaseName);
		// 表model类的名称 and 要加载库的指定表名
		$tableModelClassName = ucfirst($tableName);
		// 表model类的命名空间
		$tableModelNamespace = $namespace.$databaseName;
		// 父Model类的完整路径
		$model = $namespace.$model;

		$params = [
			'table'=>$tableModelClassName,
			'namespace'=>$tableModelNamespace,
			'path'=>$configPath
		];

		// 通过Model::__callStatic获得指定表的model对象
		$databaseTableModelObject = call_user_func("{$model}::{$configName}",$params);
		// 得到所有的已经实例pdo数据库对象
		$databases = call_user_func("{$model}::getDatabases");
		self::$models[$databaseName][$tableName] = $databaseTableModelObject;
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






