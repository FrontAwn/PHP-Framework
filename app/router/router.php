<?php 
namespace app\router;

use component\router\Router;

Router::group('english',__DIR__.'/group/english.php');
Router::group('course',__DIR__.'/group/course.php');
