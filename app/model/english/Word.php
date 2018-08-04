<?php 
namespace app\model\english;

use app\model\Model;

class Word extends Model {
	
	public function init() {
		\debug($this->db);
		\debug($this->table);
	}

}