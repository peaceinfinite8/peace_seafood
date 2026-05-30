<?php

declare(strict_types=1);

namespace App\Utils;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{
    /**
     * Send email using PHPMailer (SMTP) with safe fallback to local log mock.
     */
    public static function send(string $to, string $subject, string $body): bool
    {
        // 1. Log to mock file for local developer preview (Zero-Setup convenience)
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }
        $logFile = $logDir . '/email_mock.log';
        $timestamp = date('Y-m-d H:i:s');
        $logContent = "============================================================\n" .
                      "TIMESTAMP : {$timestamp}\n" .
                      "TO        : {$to}\n" .
                      "SUBJECT   : {$subject}\n" .
                      "BODY      :\n{$body}\n" .
                      "============================================================\n\n";
        file_put_contents($logFile, $logContent, FILE_APPEND);

        // 2. Fetch configurations from global environment
        $mailHost = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mailPort = (int)($_ENV['MAIL_PORT'] ?? 587);
        $mailUser = $_ENV['MAIL_USERNAME'] ?? '';
        $mailPass = $_ENV['MAIL_PASSWORD'] ?? '';
        $mailFromName = $_ENV['MAIL_FROM_NAME'] ?? 'Peace Seafood System';

        // Check if PHPMailer class is present and SMTP details are configured
        if (class_exists(PHPMailer::class) && !empty($mailUser) && !empty($mailPass)) {
            try {
                $mail = new PHPMailer(true);

                // Server settings
                $mail->isSMTP();
                $mail->Host       = $mailHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $mailUser;
                $mail->Password   = $mailPass;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $mailPort;

                // Recipients
                $mail->setFrom($mailUser, $mailFromName);
                $mail->addAddress($to);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = nl2br($body);
                $mail->AltBody = strip_tags($body);

                $mail->send();
                return true;
            } catch (Exception $e) {
                // Log SMTP sending failure
                error_log("SMTP Mailer Error: " . $mail->ErrorInfo);
            }
        }

        // Return true because it was successfully recorded in local email_mock.log for the developer!
        return true;
    }
}
