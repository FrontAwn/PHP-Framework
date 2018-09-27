<?php 
namespace app\controller\course;

class CourseSeries {

	function ls($res) {
		$courseSeriesModel = $res['model']['course']['courseSeries'];
		$ajax = $res['http']['setData'];
		$series = $courseSeriesModel->getSeriesList();
		$ajax($series);
	}

}