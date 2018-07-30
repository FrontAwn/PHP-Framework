<?php 

// require_once __DIR__.'/vendor/autoload.php';
require_once __DIR__.'/component/loader/ClassLoader.php';
require_once __DIR__.'/test/CommonFunction.php';
(\component\loader\ClassLoader::getInstance())->register();
$app = require_once __DIR__.'/app/App.php';
$compose = $app->make("compose",["application"=>$app]);

$compose->insertClosure(function($res){
	require_once __DIR__.'/app/router/router.php';
});

try {
	$compose->start();
} catch(\component\exception\ExceptionExtension $e) {
	$compose->end($e);
}






