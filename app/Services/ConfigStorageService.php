<?php

namespace App\Services;

class ConfigStorageService
{
    private $storagePath;

    public function __construct()
    {
        $this->storagePath = __DIR__ . '/../../storage/configs/';
    }

    public function saveConfig($name, array $config)
    {
        // Define directory if it doesn't exist
        if (!is_dir($this->storagePath)) {
            mkdir($this->storagePath, 0775, true);
        }

        // Define filename based on config name
        $safeName = preg_replace('/[^a-z0-9_\-]/i', '_', $name);
        $filePath = $this->storagePath . $safeName . '.json';

        // Prepare and save config as JSON
        if (file_put_contents($filePath, json_encode($config, JSON_PRETTY_PRINT)) !== false) {
            return ['success' => true, 'message' => "Configuration '{$name}' saved successfully."];
        }

        return ['success' => false, 'message' => "Failed to save configuration '{$name}'."];
    }

    public function getConfig($name)
    {
        $safeName = preg_replace('/[^a-z0-9_\-]/i', '_', $name);
        $filePath = $this->storagePath . $safeName . '.json';

        if (file_exists($filePath)) {
            $json = file_get_contents($filePath);
            return json_decode($json, true);
        }

        return null;
    }
}
