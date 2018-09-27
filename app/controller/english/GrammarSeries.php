<?php
namespace app\controller\english;

class GrammarSeries {

	function ls($res) {
		$grammarSeriesModel = $res['model']['english']['grammarSeries'];
		$ajax = $res['http']['setData'];
		$datas = $grammarSeriesModel->getGrammarSeriesLs();
		$ajax($datas);
	}

}