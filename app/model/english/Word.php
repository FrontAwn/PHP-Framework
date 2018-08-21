<?php 
namespace app\model\english;

use app\model\Model;

class Word extends Model {
	
	function addWord($words) {
		foreach ($words as $key => &$word) {
			$word['create_time'] = date("Y-m-d");
			$word['n'] = $word['n']['value'];
			$word['v'] = $word['v']['value'];
			$word['adj'] = $word['adj']['value'];
			$word['adv'] = $word['adv']['value'];
			$word['pron'] = $word['pron']['value'];
			$word['prep'] = $word['prep']['value'];
			$word['conj'] = $word['conj']['value'];
		}
		return $this->insertAll($words);
	}

}