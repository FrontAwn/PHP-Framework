<?php 

return array(

	"type" => "mysql",

	"host" => "10.211.55.4",

	"port" => "3306",

	"username" => "root",

	"password" => "123456",

	"database" => "english",

	"charset" => "utf8",

	"options" => [
		PDO::ATTR_PERSISTENT => true,
		PDO::ATTR_AUTOCOMMIT => 0,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::MYSQL_ATTR_INIT_COMMAND => 'set names utf8',
	],

);