<?php

namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    private static $instance = null;
    private $config;

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->config = [
            'smtp_host' => $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com',
            'smtp_port' => $_ENV['SMTP_PORT'] ?? 587,
            'smtp_user' => $_ENV['SMTP_USER'] ?? '',
            'smtp_pass' => $_ENV['SMTP_PASS'] ?? '',
            'smtp_secure' => $_ENV['SMTP_SECURE'] ?? 'tls',
            'smtp_from_email' => $_ENV['SMTP_FROM_EMAIL'] ?? 'no-reply@test.com',
            'smtp_from_name' => $_ENV['SMTP_FROM_NAME'] ?? 'Email Service',
        ];
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    public function get($key)
    {
        return $this->config[$key] ?? null;
    }
}
