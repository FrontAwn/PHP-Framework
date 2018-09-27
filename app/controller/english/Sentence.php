<?php
namespace app\controller\english;

class Sentence {

	function add($res) {
		$sentences = json_decode($res['router']['params']['sentences'],true);
		$sentenceModel = $res['model']['english']['sentence'];
		$error = $res['exception']['error'];
		$ajax = $res['http']['setData'];
		$ids = $sentenceModel->addSentences($sentences);
		if( empty($ids) ) {
			$error("保存失败");
		} else {
			$ajax(["status"=>"保存成功!"]);
		}
	}

}