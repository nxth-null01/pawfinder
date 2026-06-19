<?php
session_start();
$config = require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/models/Database.php';
require __DIR__ . '/../app/models/User.php';
require __DIR__ . '/../app/models/Report.php';
require __DIR__ . '/../app/controllers/BaseController.php';
require __DIR__ . '/../app/controllers/HomeController.php';
require __DIR__ . '/../app/controllers/AuthController.php';
require __DIR__ . '/../app/controllers/ReportController.php';
require __DIR__ . '/../app/controllers/AdminController.php';

Database::connect($config);
$route = $_GET['route'] ?? 'home';
$routes = [
  'home' => [HomeController::class,'index'],
  'reports' => [ReportController::class,'index'],
  'report-show' => [ReportController::class,'show'],
  'report-create' => [ReportController::class,'create'],
  'report-store' => [ReportController::class,'store'],
  'sighting-store' => [ReportController::class,'storeSighting'],
  'login' => [AuthController::class,'login'],
  'login-post' => [AuthController::class,'loginPost'],
  'register' => [AuthController::class,'register'],
  'register-post' => [AuthController::class,'registerPost'],
  'logout' => [AuthController::class,'logout'],
  'dashboard' => [ReportController::class,'dashboard'],
  'admin' => [AdminController::class,'index'],
  'admin-status' => [AdminController::class,'status'],
];
if (!isset($routes[$route])) { http_response_code(404); exit('404 Not Found'); }
[$class,$method] = $routes[$route];
(new $class)->$method();
