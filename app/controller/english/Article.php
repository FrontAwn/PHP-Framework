<?php 
namespace app\controller\english;

class Article {

	function add($res) {
		$ajax = $res['http']['setData'];
		$error = $res['exception']['error'];
		$articleModel = $res['model']['english']['article'];
		$article = $res['router']['params']['article'];
		$lastId = $articleModel->addArticle($article);
		if( $lastId !== 0 ) {
			$ajax(['status'=>"保存成功!"]);
		} else {
			$error("保存失败!");
		}
	}

}