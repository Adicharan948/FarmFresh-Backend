<?php
header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

/* ✅ DEBUG MODE (turn OFF after testing) */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/phpmailer/src/Exception.php";
require __DIR__ . "/phpmailer/src/PHPMailer.php";
require __DIR__ . "/phpmailer/src/SMTP.php";

/* ================= RECEIVE DATA ================= */
$email = trim($_POST['email'] ?? '');

if ($email === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email required"
    ]);
    exit;
}

/* ================= CHECK USER EXISTS ================= */
$userCheck = $conn->prepare(
    "SELECT id FROM users WHERE email = ? LIMIT 1"
);

if (!$userCheck) {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
    ]);
    exit;
}

$userCheck->bind_param("s", $email);
$userCheck->execute();
$userCheck->store_result();

if ($userCheck->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Email not registered"
    ]);
    exit;
}

/* ================= GENERATE OTP ================= */
$otp = strval(rand(100000, 999999));

/* ================= DELETE OLD OTP ================= */
$deleteOtp = $conn->prepare(
    "DELETE FROM otp_verification WHERE email = ?"
);
$deleteOtp->bind_param("s", $email);
$deleteOtp->execute();

/* ================= INSERT OTP ================= */
$insertOtp = $conn->prepare(
    "INSERT INTO otp_verification (email, otp, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))"
);

if (!$insertOtp) {
    echo json_encode([
        "success" => false,
        "message" => "OTP prepare failed"
    ]);
    exit;
}

$insertOtp->bind_param("ss", $email, $otp);

if (!$insertOtp->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "OTP insert failed"
    ]);
    exit;
}

/* ================= SEND EMAIL ================= */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "adicharan40@gmail.com";
    $mail->Password = "vtsvlasfuluuzhww"; // app password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom("adicharan40@gmail.com", "FarmFresh");
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "FarmFresh OTP Verification";
    $mail->Body = "
        <div style='font-family:Arial'>
            <h2>FarmFresh OTP</h2>
            <h1 style='color:#2e7d32;'>$otp</h1>
            <p>This OTP is valid for <b>5 minutes</b>.</p>
            <p>Please do not share it with anyone.</p>
        </div>
    ";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "OTP sent to email"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Email send failed: " . $mail->ErrorInfo
    ]);
}
