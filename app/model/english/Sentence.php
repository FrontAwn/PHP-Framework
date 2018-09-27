<?php 
namespace app\model\english;

use app\model\Model;

class Sentence extends Model {

	function addSentences($sentences) {
		foreach ($sentences as $key => &$sentence) {
			$sentence['create_time'] = date("Y-m-d");
		}
		return $this->insertAll($sentences);
	}

}

