<?php
header("Content-Type: application/json");

/* 🔒 Production safe */
error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set("Asia/Kolkata");

include "db.php";

/* ================= RECEIVE DATA ================= */
$email = trim($_POST['email'] ?? '');
$otp   = trim($_POST['otp'] ?? '');

/* ================= VALIDATION ================= */
if ($email === '' || $otp === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email and OTP required"
    ]);
    exit;
}

/* ================= VERIFY OTP ================= */
$sql = "
    SELECT id
    FROM otp_verification
    WHERE email = ?
      AND otp = ?
      AND expires_at >= NOW()
    ORDER BY id DESC
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
    exit;
}

/* OTP AS STRING */
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

/* ================= DELETE OTP AFTER SUCCESS ================= */
$del = $conn->prepare("DELETE FROM otp_verification WHERE email = ?");
$del->bind_param("s", $email);
$del->execute();

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "OTP verified successfully"
]);
