<?php 
require_once __DIR__.'/component/loader/ClassLoader.php';
require_once __DIR__.'/test/CommonFunction.php';
(\component\loader\ClassLoader::getInstance())->register();
$app = require_once __DIR__.'/app/App.php';
$compose = $app->make("compose",["application"=>$app]);

$compose->insertClosure(function($res){
	require_once __DIR__.'/app/router/router.php';
});

$compose->insertClosure(function($res){
	if ( !isset($res['database']['databaseList']) || count($res['database']['databaseList']) === 0 ) return false;
	$databaseList = $res['database']['databaseList'];
	foreach ($databaseList as $name => $database) {
		if( !$database->inTransaction() ) {
			continue;
		}
		$database->commit();
	}
});


try {
	$compose->start();
	\debug(array_keys($app->getCacheInstances()),"cache对象列表");
} catch(\component\exception\ExceptionExtension $e) {
	$compose->end($e);
}




