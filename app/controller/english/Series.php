<?php 
namespace app\controller\english;

class Series {

	function ls($res) {
		\debug(array_keys($res['http']));
		$ajax = $res['http']['setData'];
		$series = $res['model']['english']['series'];
		$seriesList = $series->getSeriesList();
		$ajax($seriesList);
	}

}