<?php 
namespace component\container\contract;

interface ContainerContract {
	public function bind($sign,$concrete=null,$cache=false);
	public function make($sign,array $parameters=[]);

	public function cache($sign,$concrete=null,$cache=true);
}