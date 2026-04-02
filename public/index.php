<?php

require __DIR__ . '/../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Controllers\SendMailController;
use App\Controllers\ConfigController;

// Set response headers for API
header('Content-Type: application/json');

// Simple Router
$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/health') !== false) {
    echo json_encode([
        'status' => 'ok', 
        'service' => 'email-php', 
        'version' => '1.1 - SMTP_LOGS_ENABLED',
        'time' => date('Y-m-d H:i:s')
    ]);
} elseif (strpos($uri, '/config') !== false) {
    $controller = new ConfigController();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $controller->save();
    } else {
        $controller->check();
    }
} else {
    $controller = new SendMailController();
    $controller->handleRequest();
}
