<?php 
namespace component\database\contract;

interface DatabaseDriverContract {
	public function connect(array $config);
	public function getDatabase(); 
}