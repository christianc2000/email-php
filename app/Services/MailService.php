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
        $logFile = __DIR__ . '/../../storage/configs/smtp_debug_log.txt';
        file_put_contents($logFile, "\n\n" . date('[Y-m-d H:i:s] ') . "=== NUEVA PETICIÓN DE ENVÍO ===\n", FILE_APPEND);

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

            // Log de configuración (OJO: No logueamos el pass por seguridad total, solo los primeros 2 caracteres)
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Config: Host=$host, Port=$port, User=$user, Secure=$secure, From=$fromEmail\n", FILE_APPEND);
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Para: " . (is_array($to) ? implode(',', $to) : $to) . "\n", FILE_APPEND);

            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = $secure;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';
            $mail->Timeout    = 15; 
            
            // Forzar un Hostname válido usando el dominio del remitente.
            // Esto evita que el Message-ID o EHLO salgan como "@192.168.0.16" y sean bloqueados por Spam en Outlook.
            $domain = substr(strrchr($fromEmail, "@"), 1);
            if (!empty($domain)) {
                $mail->Hostname = $domain;
            }
            
            // Habilitar depuración SMTP
            $mail->SMTPDebug  = 3; 
            $mail->Debugoutput = function($str, $level) use ($logFile) {
                file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "[SMTP] " . trim($str) . "\n", FILE_APPEND);
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

            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "Intentando enviar con \$mail->send()...\n", FILE_APPEND);
            $mail->send();
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "✅ ENVÍO EXITOSO SEGÚN PHPMAILER\n", FILE_APPEND);
            
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (\Exception $e) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "❌ ERROR PHPMailer: " . $e->getMessage() . "\n", FILE_APPEND);
            return [
                'success' => false, 
                'message' => "Email could not be sent. Error: {$e->getMessage()}"
            ];
        } catch (\Throwable $e) {
            file_put_contents($logFile, date('[Y-m-d H:i:s] ') . "❌ ERROR FATAL: " . $e->getMessage() . "\n", FILE_APPEND);
            return [
                'success' => false, 
                'message' => "Fatal Error: {$e->getMessage()}"
            ];
        }
    }
}
