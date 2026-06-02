<?php

declare(strict_types=1);

// Usage: php cli/test_send_email.php you@example.com

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/app.php';

use App\Utils\Email;

$to = $argv[1] ?? ($_ENV['DEV_TEST_EMAIL'] ?? 'developer@example.com');

// Send test email using the new template system
Email::sendTemplate($to, 'test_email');

echo "Test email sent (check storage/logs/email_mock.log)\n";
