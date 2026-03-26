<?php

namespace App\Validation;

class MailValidator
{
    public static function validate(array $data)
    {
        $errors = [];

        if (empty($data['to'])) {
            $errors[] = "Recipient email 'to' is required.";
        } else {
            $toRecipients = is_array($data['to']) ? $data['to'] : [$data['to']];
            foreach ($toRecipients as $email) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "Recipient email '$email' is not a valid email address.";
                }
            }
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
