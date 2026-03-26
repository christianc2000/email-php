<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Config\Config;

class MailService
{
    private $config;

    public function __construct()
    {
        $this->config = Config::getInstance();
    }

    public function sendEmail($to, $subject, $htmlBody, $plainBody = '', $attachments = [], $customConfig = null)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->SMTPDebug = 0; // Disable debug output for production
            $mail->isSMTP();

            // Override default config if provided
            $host       = $customConfig['host'] ?? $this->config->get('smtp_host');
            $port       = $customConfig['port'] ?? $this->config->get('smtp_port');
            $user       = $customConfig['user'] ?? $this->config->get('smtp_user');
            $pass       = $customConfig['pass'] ?? $this->config->get('smtp_pass');
            $secure     = $customConfig['secure'] ?? $this->config->get('smtp_secure');
            $from_email = $customConfig['from_email'] ?? $this->config->get('smtp_from_email');
            $from_name  = $customConfig['from_name'] ?? $this->config->get('smtp_from_name');

            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = $secure;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64'; // More robust for HTML with special characters

            // Recipients
            $mail->setFrom($from_email, $from_name);
            if (is_array($to)) {
                foreach ($to as $recipient) {
                    $mail->addAddress($recipient);
                }
            } else {
                $mail->addAddress($to);
            }

            // Attachments
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    if (isset($attachment['path']) && file_exists($attachment['path'])) {
                        $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                    } elseif (isset($attachment['content'])) {
                        // Support for base64 encoded content
                        $content = base64_decode($attachment['content']);
                        $mail->addStringAttachment($content, $attachment['name'] ?? 'attachment', 'base64');
                    }
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $plainBody ?: strip_tags($htmlBody);

            $mail->send();
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"];
        }
    }
}
