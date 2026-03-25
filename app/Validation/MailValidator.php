<?php

namespace App\Validation;

class MailValidator
{
    public static function validate(array $data)
    {
        $errors = [];

        if (empty($data['to'])) {
            $errors[] = "Recipient email 'to' is required.";
        } elseif (!filter_var($data['to'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Recipient email 'to' is not a valid email address.";
        }

        if (empty($data['subject'])) {
            $errors[] = "Subject is required.";
        }

        if (empty($data['body'])) {
            $errors[] = "Body message is required.";
        }

        return $errors ? ['isValid' => false, 'errors' => $errors] : ['isValid' => true];
    }
}
