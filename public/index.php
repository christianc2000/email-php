<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Controllers\SendMailController;
use App\Controllers\ConfigController;

// Set response headers for API
header('Content-Type: application/json');

// Simple Router
$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/config') !== false) {
    $controller = new ConfigController();
    $controller->save();
} else {
    $controller = new SendMailController();
    $controller->handleRequest();
}
