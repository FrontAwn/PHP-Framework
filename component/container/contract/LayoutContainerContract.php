<?php 
namespace component\container\contract;

interface CommonContainerContract {
	public static function add($key,$value,$force=true);
	public static function remove($key);
	public static function get($key);
	public static function contains($key);
	public static function addAll($values,$force=true);
	public static function getAll();
	public static function size();
}