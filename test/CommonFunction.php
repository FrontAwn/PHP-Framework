<?php 
function debug($code,$statement="") {
	if( isset($_GET['debug']) ) {
		echo "<span style='color:red'>".$statement."</span><br/>";
		echo "<pre>".print_r($code,true)."</pre><br/>";	
		echo "<hr><br/>";
	}
}

function indexBy($key,array $datas) {
	$res = [];

	foreach ($datas as $value) {
		$res[$value[$key]] = $value;
	}

	return $res; 
}

