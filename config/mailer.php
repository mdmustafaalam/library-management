<?php
// =====================================================
// config/mailer.php
// PHPMailer SMTP configuration for sending OTP emails.
// Reads credentials from .env file.
// =====================================================

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Send an OTP email to the given address.
 */
function sendOTP($toEmail, $otp, $userName = 'User')
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = env('SMTP_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth   = true;
        $mail->Username   = env('SMTP_USERNAME');
        $mail->Password   = env('SMTP_PASSWORD');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int)env('SMTP_PORT', '587');
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(
            env('SMTP_FROM_EMAIL', env('SMTP_USERNAME')),
            env('SMTP_FROM_NAME', 'Library Management System')
        );
        $mail->addAddress($toEmail, $userName);

        $mail->isHTML(true);
        $mail->Subject = 'Your Login OTP - Library Management System';

        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
        </head>
        <body style="margin:0;padding:0;background:#f1f5f9;font-family:\'Segoe UI\',Tahoma,Geneva,Verdana,sans-serif;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9;padding:40px 20px;">
                <tr>
                    <td align="center">
                        <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">
                            <tr>
                                <td style="background:linear-gradient(135deg,#4f46e5,#6366f1);padding:32px;text-align:center;">
                                    <div style="width:56px;height:56px;background:rgba(255,255,255,0.2);border-radius:14px;margin:0 auto 16px;line-height:56px;font-size:24px;color:#fff;">&#128214;</div>
                                    <h1 style="color:#ffffff;margin:0;font-size:20px;font-weight:700;">Library Management System</h1>
                                    <p style="color:rgba(255,255,255,0.8);margin:6px 0 0;font-size:13px;">One-Time Password Verification</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:36px 32px;">
                                    <p style="color:#475569;font-size:14px;margin:0 0 8px;">Hello <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                                    <p style="color:#475569;font-size:14px;margin:0 0 24px;">Use the following OTP to complete your login. This code is valid for <strong>' . env('OTP_EXPIRY_MINUTES', '5') . ' minutes</strong>.</p>
                                    <div style="background:#f8fafc;border:2px dashed #e2e8f0;border-radius:12px;padding:20px;text-align:center;margin:0 0 24px;">
                                        <p style="color:#94a3b8;font-size:11px;text-transform:uppercase;letter-spacing:2px;margin:0 0 10px;">Your OTP Code</p>
                                        <p style="color:#4f46e5;font-size:32px;font-weight:700;letter-spacing:8px;margin:0;">' . htmlspecialchars($otp) . '</p>
                                    </div>
                                    <p style="color:#94a3b8;font-size:12px;margin:0;text-align:center;">If you did not request this OTP, please ignore this email.</p>
                                </td>
                            </tr>
                            <tr>
                                <td style="background:#f8fafc;padding:20px 32px;text-align:center;border-top:1px solid #e2e8f0;">
                                    <p style="color:#94a3b8;font-size:11px;margin:0;">This is an automated message. Please do not reply.</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        $mail->AltBody = "Hello {$userName},\n\nYour OTP code is: {$otp}\nThis code expires in " . env('OTP_EXPIRY_MINUTES', '5') . " minutes.\n\nIf you did not request this, ignore this email.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("OTP Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
