<?php 
namespace app\controller;

class Index {

	public function index($res) {
		$word = $res['model']['english']['word'];
		$word->test($res);
	}

}