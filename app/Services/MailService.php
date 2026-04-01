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
            // Configuración de servidor
            $host = $customConfig['SMTP_HOST'] ?? $this->config->get('smtp_host');
            $port = $customConfig['SMTP_PORT'] ?? $this->config->get('smtp_port');
            $user = $customConfig['SMTP_USER'] ?? $this->config->get('smtp_user');
            $pass = $customConfig['SMTP_PASS'] ?? $this->config->get('smtp_pass');
            $secure = $customConfig['SMTP_SECURE'] ?? $this->config->get('smtp_secure');
            $fromEmail = $customConfig['SMTP_FROM_EMAIL'] ?? $this->config->get('smtp_from_email');
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
            $mail->Timeout    = 10; 
            
            // Habilitar depuración SMTP para ver la respuesta real de Zoho
            $mail->SMTPDebug  = 3; // Nivel 3 para ver detalles de conexión
            $mail->Debugoutput = function($str, $level) {
                // Forzamos la ruta a la carpeta configs donde sabemos que hay permisos
                $logFile = __DIR__ . '/../../storage/configs/smtp_debug_log.txt';
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $str . PHP_EOL, FILE_APPEND);
            };

            // Forzar TLS para evitar problemas de certificados
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            // Configuración del remitente
            $mail->setFrom($fromEmail, $fromName);
            
            if (is_array($to)) {
                foreach ($to as $address) {
                    $mail->addAddress($address);
                }
            } else {
                $mail->addAddress($to);
            }

            // Adjuntos y CIDs
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
