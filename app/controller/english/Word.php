<?php
namespace app\controller\english;

class Word {

	function add($res) {
		$wordModel = $res['model']['english']['word'];
		$words = json_decode($res['router']['params']['words'],true);
		$error = $res['exception']['error'];
		$ajax = $res['http']['setData'];
		$ids = $wordModel->addWord($words);
		if( empty($ids) ) {
			$error("保存失败");
		} else {
			$ajax(["status"=>"保存成功!"]);
		}
	}

	function search($res) {
		$wordModel = $res['model']['english']['word'];
		$ajax = $res['http']['setData'];
		$conditions = $res['router']['params']['conditions'];
		$words = $wordModel->searchWord($conditions);
		if(!empty($words)) {
			$words['ids'] = array_keys($words);
		}else{
			$words['ids'] = [];
		}
		$ajax($words);
	}
}