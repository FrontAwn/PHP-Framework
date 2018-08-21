<?php 
namespace app\router\group;

use component\router\Router;

Router::namespace('\app\controller\english');

Router::get("/series/ls","Series@ls",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","series");
});

Router::post("/word/add","Word@add",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","word");
});

Router::get("/word/add","Word@add",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","word");
});
