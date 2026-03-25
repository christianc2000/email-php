<?php

namespace App\Controllers;

use App\Services\ConfigStorageService;

class ConfigController
{
    private $storageService;

    public function __construct()
    {
        $this->storageService = new ConfigStorageService();
    }

    public function save()
    {
        // Only allow POST method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed. Use POST.']);
            return;
        }

        // Get JSON data
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input.']);
            return;
        }

        // Validate data presence
        if (empty($data['name'])) {
            http_response_code(422);
            echo json_encode(['error' => 'Configuration name is required.']);
            return;
        }

        // Config keys to save
        $configKeys = [
            'host' => 'SMTP_HOST',
            'port' => 'SMTP_PORT',
            'user' => 'SMTP_USER',
            'pass' => 'SMTP_PASS',
            'secure' => 'SMTP_SECURE',
            'from_email' => 'SMTP_FROM_EMAIL',
            'from_name' => 'SMTP_FROM_NAME',
        ];

        $configData = [];
        foreach ($configKeys as $key => $envVar) {
            if (empty($data[$key])) {
                http_response_code(422);
                echo json_encode(['error' => "Configuration field '{$key}' is required."]);
                return;
            }
            $configData[$key] = $data[$key];
        }

        // Save
        $response = $this->storageService->saveConfig($data['name'], $configData);

        if ($response['success']) {
            http_response_code(201);
            echo json_encode($response);
        } else {
            http_response_code(500);
            echo json_encode($response);
        }
    }
}
