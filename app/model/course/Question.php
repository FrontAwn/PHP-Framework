<?php 
namespace app\model\course;

use app\model\Model;

class Question extends Model {

	function addQuestion($question) {
		$question = json_decode($question,true);
		$question['create_time'] = date("Y-m-d");
		$question['answer_other'] = json_encode($question['answer_other']);
		return $this->insert($question);
	}

}