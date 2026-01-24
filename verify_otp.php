<?php
header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

/* ✅ TEMP DEBUG (turn OFF after testing) */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

/* ================= RECEIVE DATA ================= */
$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

if ($email === '' || $otp === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email and OTP required"
    ]);
    exit;
}

/* ================= VERIFY OTP ================= */
$stmt = $conn->prepare(
    "SELECT email
     FROM otp_verification
     WHERE email = ?
       AND otp = ?
       AND expires_at >= NOW()
     LIMIT 1"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error (OTP check)"
    ]);
    exit;
}

$stmt->bind_param("ss", $email, $otp);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid or expired OTP"
    ]);
    exit;
}

/* ================= MARK USER AS VERIFIED ================= */
$verifyUser = $conn->prepare(
    "UPDATE users SET is_verified = 1 WHERE email = ?"
);

if (!$verifyUser) {
    echo json_encode([
        "success" => false,
        "message" => "User verification failed"
    ]);
    exit;
}

$verifyUser->bind_param("s", $email);
$verifyUser->execute();

/* ================= DELETE USED OTP ================= */
$deleteOtp = $conn->prepare(
    "DELETE FROM otp_verification WHERE email = ?"
);
$deleteOtp->bind_param("s", $email);
$deleteOtp->execute();

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "Email verified successfully"
]);
