<?php 
namespace app\router;

use component\router\Router;

Router::get('/','Index@index',function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel('english','word');
	$loadModel('english','interpretation');
},['group'=>""]);

Router::group('user',__DIR__.'/group/user.php');
