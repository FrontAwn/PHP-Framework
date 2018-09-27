<?php 
namespace app\controller\course;

class Question {

	function add($res) {
		$questionModel = $res['model']['course']['question'];
		$ajax = $res['http']['setData'];
		$error = $res['exception']['error'];
		$question = $res['router']['params']['question'];
		$lastId = $questionModel->addQuestion($question);
		if( $lastId !== 0 ) {
			$ajax(['status'=>"保存成功!"]);
		} else {
			$error("保存失败!");
		}
	}

}