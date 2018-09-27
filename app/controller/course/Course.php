<?php 
namespace app\controller\course;

class Course {

	function add($res) {
		$courseModel = $res['model']['course']['course'];
		$ajax = $res['http']['setData'];
		$error = $res['exception']['error'];
		$course = $res['router']['params']['course'];
		$lastId = $courseModel->addCourse($course);
		if( $lastId !== 0 ) {
			$ajax(['status'=>"保存成功!"]);
		} else {
			$error("保存失败!");
		}
	}

	function modify($res) {
		$courseModel = $res['model']['course']['course'];
		$ajax = $res['http']['setData'];
		$error = $res['exception']['error'];
		$course = $res['router']['params']['course'];
		$row = $courseModel->modifyCourse($course);
		if( $row !== 0 ) {
			$ajax(['status'=>"修改成功!"]);
		} else {
			$error("修改失败!");
		}
	}


	// function ls($res) {
	// 	$courseModel = $res['model']['course']['course'];
	// 	$ajax = $res['http']['setData'];
	// 	$conditions = $res['router']['params']['conditions'];
	// 	$courses = $courseModel->getCourses($conditions);
	// 	foreach ($courses as $key => &$value) {
	// 		if( isset($value['questions']) ) {
	// 			$value['questions'] = json_decode($value['questions'],true);
	// 		}
	// 	}
	// 	$ajax($courses);
	// }

	function courseById($res) {
		$courseModel = $res['model']['course']['course'];
		$ajax = $res['http']['setData'];
		$id = $res['router']['params']['id'];
		$course = $courseModel->getCourseById($id);
		$ajax($course);
	}


	function courseTitles($res) {
		$courseModel = $res['model']['course']['course'];
		$ajax = $res['http']['setData'];
		$courseTitles = $courseModel->getCourseTitles();
		$ajax($courseTitles);
	}

	function courseTitleBySeriesId($res) {
		$courseModel = $res['model']['course']['course'];
		$ajax = $res['http']['setData'];
		$courseSeriesId = $res['router']['params']['series_id'];
		$courseTitles = $courseModel->getCourseTitlesBySeriesId($courseSeriesId);
		$ajax($courseTitles);
	}

	function courseCountBySeries($res) {
		$courseModel = $res['model']['course']['course'];
		$ajax = $res['http']['setData'];
		$courseSeriesId = $res['router']['params']['series_id'];
		$res = $courseModel->getCourseCountBySeriesId($courseSeriesId);
		$ajax(['count'=>$res[0]['count']]);
	}




















}