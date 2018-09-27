<?php 
namespace app\model\english;

use app\model\Model;

class GrammarSeries extends Model {

	function getGrammarSeriesLs() {
		$options = [
			"column"=>['id',"title"],
			"child" => "order by id asc",
		];
		return $this->query($options);
	}

}