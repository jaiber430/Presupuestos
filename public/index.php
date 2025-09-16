<?php
require __DIR__ . '/../vendor/autoload.php';

$routes= require __DIR__ . '/../routes.php';


$uri= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$route= trim($uri, '/');

$method= $_SERVER['REQUEST_METHOD'];
$key= ($method === 'POST') ? "$route-post" : $route;

if (isset($routes[$key])) {
    $routes[$key]();
} elseif(preg_match('#^aulas/([\w\-]+)$#', $route, $matches)) {
    $group = $matches[1];
    (new \Klassroom\controller\ClassroomController())->showClassroom($group);
    exit;
}else{
    http_response_code(404);
    echo "Ruta no encontrada: /$route";
}

