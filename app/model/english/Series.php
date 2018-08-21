<?php 
namespace app\model\english;
use app\model\Model;

class Series extends Model {

	function getSeriesList() {
		$options = [
			"child" => "order by id asc",
		];
		return $this->query($options);
	}

}