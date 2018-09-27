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
			$word['other'] = $word['other']['value'];
		}
		\debug($words);
		return $this->insertAll($words);
	}

	function searchWord($conditions) {
		$values = [];
		$values['where'] = [];

		$conditions = json_decode($conditions,true);

		if( isset($conditions['series_id']) ) {
			array_push($values['where'], $this->equals("series_id",$conditions['series_id']));
		}

		if( isset($conditions['series']) && !empty($conditions['series']) ) {
			array_push($values['where'], $this->in("series_id",$conditions['series']));
		}

		if( isset($conditions['types']) && !empty($conditions['types']) ) {
			$defaultColumns = ["id","english","series_id"];
			foreach ($conditions['types'] as $key => $value) {
				array_push($values['where'], $this->notEquals($value,""));
			}
			$values['column'] = array_merge($defaultColumns,$conditions['types']);
		}



		if( $conditions['random'] === true ) {
			$totalValues = [];
			$totalValues['column'] = ["id"];
			$totalValues['child'] = "order by id asc";
			if( !empty($values['where']) ) {
				$totalValues['where'] = $values['where'];
			}
			$totalRes = $this->query($totalValues);
			$ids = array_keys($this->indexBy("id",$totalRes));
			// \debug($ids,"totalIds");
			$maxId = end($ids);
			$randomRepeatIds = isset($conditions['repeat_ids']) ? $conditions['repeat_ids'] : [];
			$randomLength = isset($conditions['number']) ? $conditions['number'] : 0;
			$randomIds = $this->randomOfArray($ids,$randomLength,$randomRepeatIds);
			\debug($randomIds,"randomIds");
			if( !empty($randomIds) ) {
				array_push($values['where'],$this->in("id",$randomIds));
			} else {
				array_push($values['where'],$this->greaterThan("id",$maxId));
			}
		} else {
			if( isset($conditions['number']) && $conditions['number'] !== 0 ) {
				$values['number'] = $conditions['number'];
			}

			if( isset($conditions['repeat_ids']) && !empty($conditions['repeat_ids']) ) {
				array_push($values['where'], $this->notIn("id",$conditions['repeat_ids']));
			}
		}

		if( empty($values['where']) ) {
			unset($values['where']);
		}

		$res = $this->query($values);
		$res = $this->indexBy("id",$res);
		\debug($res,"res");
		return $res;
	}

}









