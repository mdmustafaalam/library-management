<?php
// =====================================================
// config/env.php
// Loads .env file and makes values available via env().
// =====================================================

function loadEnv($path)
{
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') continue;

        if (strpos($line, '=') === false) continue;

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Remove surrounding quotes
        if (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
            $value = substr($value, 1, -1);
        } elseif (strlen($value) >= 2 && $value[0] === "'" && $value[strlen($value) - 1] === "'") {
            $value = substr($value, 1, -1);
        }

        $_ENV[$key] = $value;
        putenv("$key=$value");
    }
}

// Load from project root .env
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

/**
 * Get an environment variable with an optional default.
 */
function env($key, $default = '')
{
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}
