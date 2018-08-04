<?php 
namespace app\controller;

class Index {

	public function index($res) {
		$error = $res['exception']['error'];
		$error("aaaa");
	}

}