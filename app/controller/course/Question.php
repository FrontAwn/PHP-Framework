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

	function ls($res) {
		$questionModel = $res['model']['course']['question'];
		$questionTypeModel = $res['model']['course']['questionType'];
		$ajax = $res['http']['setData'];
		$error = $res['exception']['error'];
		$condition = $res['router']['params']['condition'];
		$questionTypes = \indexBy('id',$questionTypeModel->getQuestionTypeLs());
		$questions = $questionModel->getQuestionLs($condition);
		foreach ($questions as $key => &$question) {
			$question['type_name'] = $questionTypes[$question['type_id']]['name'];
			$question['answer_other'] = json_decode($question['answer_other'],true);
		}
		$ajax(['questions'=>$questions]);
	}

}