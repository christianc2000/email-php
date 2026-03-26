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
            // Override default config if provided
            $host = $customConfig['SMTP_HOST'] ?? $this->config->get('smtp_host');
            $port = $customConfig['SMTP_PORT'] ?? $this->config->get('smtp_port');
            $user = $customConfig['SMTP_USER'] ?? $this->config->get('smtp_user');
            $pass = $customConfig['SMTP_PASS'] ?? $this->config->get('smtp_pass');
            $secure = $customConfig['SMTP_SECURE'] ?? $this->config->get('smtp_secure');
            $fromName = $customConfig['SMTP_FROM_NAME'] ?? $this->config->get('smtp_from_name');

            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = $secure;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->Timeout    = 10; // 10 seconds timeout for fast failure

            // Forzar IPv4 para evitar timeouts en servidores Linux/Render
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ],
                'socket' => [
                    'bindto' => '0:0' // Forzar IPv4 (o usar la interfaz por defecto)
                ]
            ];

            // Recipients - IMPORTANTE: Zoho requiere que el From sea el mismo que el Username
            $mail->setFrom($user, $fromName);
            
            if (is_array($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            } else {
                $mail->addAddress($to);
            }

            // Attachments
            foreach ($attachments as $attachment) {
                if (isset($attachment['content'])) {
                    $mail->addStringAttachment(
                        base64_decode($attachment['content']), 
                        $attachment['name'],
                        'base64',
                        '',
                        isset($attachment['cid']) ? 'inline' : 'attachment'
                    );
                    if (isset($attachment['cid'])) {
                        $mail->addCustomHeader("Content-ID", "<{$attachment['cid']}>");
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
        } catch (\Throwable $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => "Email could not be sent. Error: {$e->getMessage()}"
            ];
        }
    }
}
