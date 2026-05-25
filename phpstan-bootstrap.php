<?php

declare(strict_types=1);

// Minimal bootstrap for static analysis tooling.
// Defines constants used across the app without executing request/route code.

if (!\defined('BASE_PATH')) {
    \define('BASE_PATH', __DIR__);
}

// App-wide constants (roles, statuses, etc.)
@require_once __DIR__ . '/config/constants.php';

