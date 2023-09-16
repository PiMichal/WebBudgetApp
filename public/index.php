<?php

require dirname(__DIR__) . '/vendor/autoload.php';


error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

session_start();

$router = new Core\Router();

$router->add('api/limit/{category:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}', ['controller' => 'Expense', 'action' => 'limit']);
$router->add('api/limitValue/{category:[\wżźćńółęąśŻŹĆĄŚĘŁÓŃ ]+}/{date:[0-9-]+}', ['controller' => 'Expense', 'action' => 'limitValue']);

$router->add('', ['controller' => 'Login', 'action' => 'new']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);
