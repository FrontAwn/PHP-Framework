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

	function getQuestionLs($condistion) {
		$condition = json_decode($condistion,true);
		$where = [
			$this->equals('series_id',$condition['series_id']),
		];
		if( $condition['course_id'] != 0 ) {
			array_push($where, $this->equals('course_id',$condition['course_id']));			
		}
		return $this->query(['where'=>$where]);
	}

}