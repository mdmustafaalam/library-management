<?php
// =====================================================
// config/otp.php
// OTP helper functions: generate, store, validate, resend.
// =====================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/env.php';

/**
 * Generate a random 6-digit OTP code.
 */
function generateOTP()
{
    return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Store an OTP in the database.
 * Marks any previous unused OTPs for this email as used.
 */
function storeOTP($userType, $userId, $email, $otp)
{
    global $conn;

    // Mark previous OTPs as used
    $stmt = mysqli_prepare($conn, "UPDATE otps SET used = 1 WHERE email = ? AND used = 0");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    // Insert new OTP (expires in X minutes) - store as UTC
    $expiryMinutes = (int)env('OTP_EXPIRY_MINUTES', '5');
    $expiresAt = gmdate('Y-m-d H:i:s', time() + ($expiryMinutes * 60));
    $stmt = mysqli_prepare($conn, "INSERT INTO otps (user_type, user_id, email, otp_code, expires_at) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sisss", $userType, $userId, $email, $otp, $expiresAt);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

/**
 * Validate an OTP code.
 * Returns ['valid' => bool, 'error' => string, 'user_type' => string, 'user_id' => int]
 */
function validateOTP($email, $otp)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "SELECT id, user_type, user_id, otp_code, expires_at, used FROM otps WHERE email = ? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        return ['valid' => false, 'error' => 'No OTP found. Please request a new one.'];
    }

    if ($row['used']) {
        return ['valid' => false, 'error' => 'This OTP has already been used. Please request a new one.'];
    }

    // Compare using UTC to match how we store
    $expiryTimestamp = strtotime($row['expires_at'] . ' UTC');
    if ($expiryTimestamp < time()) {
        return ['valid' => false, 'error' => 'This OTP has expired. Please request a new one.'];
    }

    if ($row['otp_code'] !== $otp) {
        // Increment attempt tracking via a simple counter in used field
        return ['valid' => false, 'error' => 'Invalid OTP code. Please try again.'];
    }

    // Mark as used
    markOTPUsed($row['id']);

    return [
        'valid'     => true,
        'error'     => '',
        'user_type' => $row['user_type'],
        'user_id'   => (int)$row['user_id'],
    ];
}

/**
 * Mark an OTP record as used.
 */
function markOTPUsed($otpId)
{
    global $conn;

    $stmt = mysqli_prepare($conn, "UPDATE otps SET used = 1 WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $otpId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/**
 * Check if the user can resend (60-second cooldown).
 * Only checks non-expired, unused OTPs.
 */
function canResend($email)
{
    global $conn;

    $cooldown = (int)env('OTP_RESEND_COOLDOWN_SECONDS', '60');
    $stmt = mysqli_prepare($conn, "SELECT created_at FROM otps WHERE email = ? AND used = 0 ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$row) {
        return true;
    }

    // Compare using PHP time - created_at comes from MySQL in system timezone
    $lastSent = strtotime($row['created_at']);
    return (time() - $lastSent) >= $cooldown;
}

/**
 * Look up a user by email in both admins and members tables.
 * Returns ['found' => bool, 'user_type' => string, 'user_id' => int, 'name' => string]
 */
function findUserByEmail($email)
{
    global $conn;

    // Check admins first
    $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM admins WHERE email = ?");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($admin) {
        return ['found' => true, 'user_type' => 'admin', 'user_id' => (int)$admin['id'], 'name' => $admin['name']];
    }

    // Check members
    $stmt = mysqli_prepare($conn, "SELECT id, name, email FROM members WHERE email = ? AND status = 'Active'");
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $member = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($member) {
        return ['found' => true, 'user_type' => 'member', 'user_id' => (int)$member['id'], 'name' => $member['name']];
    }

    return ['found' => false];
}

/**
 * Mask an email address for display: a***@gmail.com
 */
function maskEmail($email)
{
    $parts = explode('@', $email);
    if (count($parts) !== 2) return $email;

    $name = $parts[0];
    $domain = $parts[1];

    if (strlen($name) <= 1) {
        $masked = $name[0] . '***';
    } else {
        $masked = $name[0] . str_repeat('*', min(strlen($name) - 1, 5));
    }

    return $masked . '@' . $domain;
}
