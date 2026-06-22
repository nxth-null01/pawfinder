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
  'about' => [HomeController::class,'about'],
  'success-stories' => [HomeController::class,'successStories'],
  'report-show' => [ReportController::class,'show'],
  'report-create' => [ReportController::class,'create'],
  'report-store' => [ReportController::class,'store'],
  'sighting-store' => [ReportController::class,'storeSighting'],
  'comment-store' => [ReportController::class,'storeComment'],
  'comment-helpful' => [ReportController::class,'toggleHelpful'],
  'comment-update' => [ReportController::class,'updateComment'],
  'comment-delete' => [ReportController::class,'deleteComment'],
  'comment-report' => [ReportController::class,'reportComment'],
  'comment-pin' => [ReportController::class,'pinComment'],
  'follow-toggle' => [ReportController::class,'toggleFollow'],
  'sighting-verify' => [ReportController::class,'verifySighting'],
  'close-request-store' => [ReportController::class,'storeCloseRequest'],
  'login' => [AuthController::class,'login'],
  'login-post' => [AuthController::class,'loginPost'],
  'register' => [AuthController::class,'register'],
  'register-post' => [AuthController::class,'registerPost'],
  'logout' => [AuthController::class,'logout'],
  'profile' => [AuthController::class,'profile'],
  'profile-update' => [AuthController::class,'updateProfile'],
  'password-update' => [AuthController::class,'updatePassword'],
  'dashboard' => [ReportController::class,'dashboard'],
  'notifications' => [ReportController::class,'notifications'],
  'notifications-read' => [ReportController::class,'markNotificationsRead'],
  'admin' => [AdminController::class,'index'],
  'admin-analytics' => [AdminController::class,'analytics'],
  'admin-status' => [AdminController::class,'status'],
  'admin-close-request' => [AdminController::class,'closeRequest'],
  'admin-delete' => [AdminController::class,'delete'],
];
if (!isset($routes[$route])) { http_response_code(404); exit('404 Not Found'); }
[$class,$method] = $routes[$route];
(new $class)->$method();
