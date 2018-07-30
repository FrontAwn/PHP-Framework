<?php 
namespace app\router;

use component\router\Router;


Router::get('/','Index@index',['group'=>""]);

Router::group('user',__DIR__.'/group/user.php');
