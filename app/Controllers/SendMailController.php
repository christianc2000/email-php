<?php

namespace App\Controllers;

use App\Services\MailService;
use App\Services\ConfigStorageService;
use App\Validation\MailValidator;

class SendMailController
{
    private $mailService;
    private $configStorage;

    public function __construct()
    {
        $this->mailService = new MailService();
        $this->configStorage = new ConfigStorageService();
    }

    public function handleRequest()
    {
        // Only allow POST method
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed. Use POST.']);
            return;
        }

        // Get JSON data from request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if ($data === null) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON input.']);
            return;
        }

        // Validate data
        $validationResult = MailValidator::validate($data);

        if ($validationResult['isValid'] === false) {
            http_response_code(422);
            echo json_encode(['error' => 'Validation failed', 'details' => $validationResult['errors']]);
            return;
        }

        // Handle Custom Config loading
        $customConfig = null;
        if (!empty($data['config_name'])) {
            $customConfig = $this->configStorage->getConfig($data['config_name']);
            if (!$customConfig) {
                http_response_code(404);
                echo json_encode(['error' => "Configuration '{$data['config_name']}' not found."]);
                return;
            }
        }

        // Prepare email arguments
        $to = $data['to'];
        $subject = $data['subject'];
        $htmlBody = $data['body'];
        $plainBody = $data['alt_body'] ?? strip_tags($htmlBody);
        $attachments = $data['attachments'] ?? [];

        // Send email
        $response = $this->mailService->sendEmail($to, $subject, $htmlBody, $plainBody, $attachments, $customConfig);

        if ($response['success']) {
            http_response_code(200);
            echo json_encode(['message' => $response['message']]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => $response['message']]);
        }
    }
}
