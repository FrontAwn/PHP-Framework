<?php 
namespace app\model\course;

use app\model\Model;

class Course extends Model {

	function addCourse($datas) {
		$datas = json_decode($datas,true);
		$datas['questions'] = json_encode($datas['questions']);
		$datas["create_time"] = date("Y-m-d");
		return $this->insert($datas);
	}

	function modifyCourse($datas) {
		$datas = json_decode($datas,true);
		$where = [$this->equals("id",$datas['id'])];
		$datas['questions'] = json_encode($datas['questions']);
		unset($datas['id']);
		return $this->update($datas,$where);
	}

	function getCourses($conditions=[]) {
		// $values = [];
		// $where = [];

		// if( !empty($conditions) ) {
		// 	$conditions = json_decode($conditions,true);

		// } else {
		// 	return $this->quexry(["child"=>"order by level asc"]);
		// }
	}

	function getCourseById($id) {
		return $this->query([
			"where"=>[
				$this->equals("id",$id)
			],
		],"once");
	}

	function getCourseTitles() {
		return $this->query([
			'column'=>["id","title"],
		]);
	}

	function getCourseTitlesBySeriesId($seriesId) {
		return $this->query([
			"column"=>['id','title'],
			'child'=>"order by level asc",
			"where"=>[
				$this->equals("series_id",$seriesId)
			]
		]);
	}

	function getCourseCountBySeriesId($seriesId) {
		return $this->query([
			"column"=>['count(*) as count'],
			"where"=>[$this->equals("series_id",$seriesId)]
		]);
	}







}