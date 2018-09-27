<?php 
namespace app\model\english;

use app\model\Model;

class Article extends Model {

	function addArticle($article) {
		$article = json_decode($article,true);
		$article['create_time'] = date("Y-m-d");

		$article['core_words'] = json_encode($article['core_words']);
		return $this->insert($article);
	}

}