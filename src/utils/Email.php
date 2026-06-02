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
                $mail->CharSet = PHPMailer::CHARSET_UTF8;

                // If caller passed plain text, try to wrap it with the base email layout
                $htmlBody = $body;
                $looksLikeHtml = (stripos($body, '<') !== false);
                if (!$looksLikeHtml) {
                    $layoutPath = BASE_PATH . '/src/views/emails/base_layout.php';
                    if (is_file($layoutPath)) {
                        $content = nl2br(htmlspecialchars($body));
                        ob_start();
                        include $layoutPath;
                        $htmlBody = ob_get_clean();
                    } else {
                        // fallback to simple nl2br body
                        $htmlBody = nl2br(htmlspecialchars($body));
                    }
                }

                $mail->Subject = $subject;
                $mail->Body    = $htmlBody;
                $mail->AltBody = strip_tags($htmlBody);

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

    /**
     * Render an email template from `src/views/emails/{name}.php` and send it.
     * Templates may set `$subject` and expect variables passed via `$vars`.
     */
    public static function sendTemplate(string $to, string $templateName, array $vars = []): bool
    {
        $templatePath = BASE_PATH . '/src/views/emails/' . $templateName . '.php';
        if (!is_file($templatePath)) {
            error_log("Email template not found: {$templatePath}");
            return false;
        }

        // Make provided variables available to the template
        extract($vars, EXTR_SKIP);

        // Capture template output
        ob_start();
        include $templatePath;
        $html = ob_get_clean();

        // Template may set $subject; fall back to passed vars or default
        $subject = $subject ?? ($vars['subject'] ?? 'Peace Seafood Notification');

        return self::send($to, $subject, $html);
    }
}
