<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");


error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/../vendor/autoload.php';

// Create Router instance
$router = new \Bramus\Router\Router();

$router->setNamespace('Controllers');

// User Management endpoints
$router->get('/test', function() {
    echo 'Test route is working!';
});

$router->post('/login', 'UserController@login');
$router->get('/users', 'UserController@getAllUsers');

$router->get('/tasks', 'TaskController@getPaginatedTasksByUserId');
$router->get('/admin/tasks', 'TaskController@getAllTasks');
$router->get('/admin/tasks/username', 'TaskController@getAllTasksWithUsernames');
$router->put('/admin/assignTask/(\d+)/(\d+)', 'TaskController@updateTaskOwner');
$router->get('/user/tasks', 'TaskController@getAllTasksByUserId');
$router->delete('/tasks/(\d+)', 'TaskController@delete');
$router->put('/tasks/(\d+)', 'TaskController@update');
$router->post('/tasks', 'TaskController@createTask');


// Run the router
$router->run();
?>