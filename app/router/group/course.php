<?php 
namespace app\router\group;

use component\router\Router;

Router::namespace('\app\controller\course');

// 添加课程
Router::post('add',"Course@add",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","course");
});

Router::post('modify',"Course@modify",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","course");
});

//得到所有课程
Router::get('ls',"Course@ls",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","course");
});

// 根据series_id获得课程的数量
Router::get('count/by/series',"Course@courseCountBySeries",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","course");
});

// 根据id获得指定课程信息
Router::get('by/id',"Course@courseById",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","course");
});

//得到所有课程标题
Router::get('titles',"Course@courseTitles",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","course");
});

// 根据课程series_id获得所有课程标题
Router::get('titles/by/series/id',"Course@courseTitleBySeriesId",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","course");
});

//添加问题
Router::post('/question/add',"Question@add",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","question");
});

//得到所有课程类型
Router::get('/series/ls',"CourseSeries@ls",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","courseSeries");
});

// 得到所有问题类型
Router::get('/question_type/ls',"QuestionType@ls",function($res){
	$loadModel = $res['database']['loadModel'];
	$loadModel("course","questionType");
});

