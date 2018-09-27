<?php 
namespace app\model\course;

use app\model\Model;

class QuestionType extends Model {

	function getQuestionTypeLs() {
		return $this->query(['child'=>"order by id asc"]);
	}

}