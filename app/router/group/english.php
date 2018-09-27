<?php 
namespace app\router\group;

use component\router\Router;

Router::namespace('\app\controller\english');

Router::get("/series/ls","Series@ls",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","series");
});

Router::get("/grammar_series/ls","GrammarSeries@ls",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","grammarSeries");
});


Router::post("/word/add","Word@add",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","word");
});

Router::get("/word/search","Word@search",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","word");
});


Router::post("/sentence/add","Sentence@add",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","sentence");
});

Router::post("/article/add","Article@add",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("english","article");
});

