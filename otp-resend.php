<?php
// =====================================================
// otp-resend.php
// AJAX endpoint to resend OTP (with 60s cooldown).
// Returns JSON response.
// =====================================================

session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['otp_email'])) {
    echo json_encode(['success' => false, 'error' => 'Session expired. Please go back.']);
    exit;
}

require_once 'config/db.php';
require_once 'config/otp.php';
require_once 'config/mailer.php';

$email    = $_SESSION['otp_email'];
$userType = $_SESSION['otp_user_type'] ?? 'admin';

// Check cooldown
if (!canResend($email)) {
    echo json_encode(['success' => false, 'error' => 'Please wait before requesting a new OTP.']);
    exit;
}

// Find user again
$user = findUserByEmail($email);

if (!$user['found']) {
    echo json_encode(['success' => false, 'error' => 'Account not found.']);
    exit;
}

// Generate and store new OTP
$otp     = generateOTP();
$stored  = storeOTP($user['user_type'], $user['user_id'], $email, $otp);

if (!$stored) {
    echo json_encode(['success' => false, 'error' => 'Failed to generate OTP.']);
    exit;
}

// Send email
$sent = sendOTP($email, $otp, $user['name']);

if ($sent) {
    echo json_encode(['success' => true, 'message' => 'OTP resent successfully.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to send email. Please try again.']);
}
