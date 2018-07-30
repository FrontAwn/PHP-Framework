<?php 
namespace test\store;

class StoreBox {
	private $itemName = "";
	private $itemPrice = null;
	private $num = 1;
	private $type = "fruit";

	public function __construct(StoreItem $AppleItemContainer,int $num=null, string $type=null) {
		$this->itemName = $AppleItemContainer->getName();
		$this->itemPrice = $AppleItemContainer->getPrice();
		if( !is_null($num) ) $this->num = $num;
		if( !is_null($type) ) $this->type = $type;

	}

	public function sumPrice() {
		return $this->itemPrice * $this->num;
	}

	public function getType() {
		return $this->type;
	}

	public function getNum() {
		return $this->num;
	}

	public function getItemName() {
		return $this->itemName;
	}

	public function getItemPrice() {
		return $this->itemPrice;
	}

}