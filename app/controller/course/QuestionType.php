<?php 
namespace app\controller\course;

class QuestionType {

	function ls($res) {
		$questionTypeModel = $res['model']['course']['questionType'];
		$ajax = $res['http']['setData'];
		$types = $questionTypeModel->getQuestionTypeLs();
		$ajax($types);
	}

}