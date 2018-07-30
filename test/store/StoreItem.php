<?php 
namespace test\store;

class StoreItem implements StoreItemContract {

	private $name = "";

	private $price = 0;

	public function __construct($name="Apple",$price=5) {
		$this->name = $name;
		$this->price = $price;
	}

	public function getName(){
		return $this->name;
	}

	public function getPrice(){
		return $this->price;
	}

}