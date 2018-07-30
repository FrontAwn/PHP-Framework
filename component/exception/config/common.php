<?php 

return array(
	"code" => [
		'ERROR_ROUTER_NOT_EXISTS' => 1001,
		'ERROR_ROUTER_PARAMETER_EXCEPTION' => 1002,
		'ERROR_ROUTER_NOT_MATCH' => 1003,
		'ERROR_ROUTER_METHOD_DENIED' => 1004,
	],

	"message" => [
		1001 => "路由不存在，请查看路由的配置是否正确",
		1002 => "路由参数异常，查看参数是否为空或者参数的数量",
		1003 => "没有匹配到路由，请检查路由配置是否正确",
		1004 => "路由设置的Http请求方法不在允许范围内，通常使用GET,POST"
	]
);