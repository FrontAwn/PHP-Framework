<?php 
namespace app\model\course;

use app\model\Model;

class CourseSeries extends Model {

	function getSeriesList() {
		return $this->query(['child'=>"order by id asc"]);
	}

}