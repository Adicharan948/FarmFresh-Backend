<?php
header("Content-Type: application/json");

/* 🔒 Hide PHP errors from app */
error_reporting(0);
ini_set('display_errors', 0);

date_default_timezone_set("Asia/Kolkata");

include "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/phpmailer/src/Exception.php";
require __DIR__ . "/phpmailer/src/PHPMailer.php";
require __DIR__ . "/phpmailer/src/SMTP.php";

/* ================= RECEIVE DATA ================= */
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role     = trim($_POST['role'] ?? '');

/* ================= VALIDATION ================= */
if ($name === '' || $email === '' || $password === '' || $role === '') {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email address"
    ]);
    exit;
}

/* ================= CHECK EMAIL ================= */
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email already registered"
    ]);
    exit;
}

/* ================= INSERT USER ================= */
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$insertUser = $conn->prepare(
    "INSERT INTO users (name, email, password, role, is_verified)
     VALUES (?, ?, ?, ?, 0)"
);
$insertUser->bind_param("ssss", $name, $email, $hashedPassword, $role);

if (!$insertUser->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Registration failed"
    ]);
    exit;
}

/* ================= OTP GENERATION ================= */
$otp = (string) rand(100000, 999999); // ✅ STRING

/* Remove old OTP */
$deleteOtp = $conn->prepare("DELETE FROM otp_verification WHERE email=?");
$deleteOtp->bind_param("s", $email);
$deleteOtp->execute();

/* Insert new OTP */
$insertOtp = $conn->prepare(
    "INSERT INTO otp_verification (email, otp, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))"
);
$insertOtp->bind_param("ss", $email, $otp);
$insertOtp->execute();

/* ================= SEND OTP EMAIL ================= */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = "adicharan40@gmail.com";   // your Gmail
    $mail->Password   = "vtsvlasfuluuzhww";         // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->CharSet = "UTF-8";

    $mail->setFrom("adicharan40@gmail.com", "FarmFresh");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "FarmFresh OTP Verification";
    $mail->Body = "
        <div style='font-family:Arial'>
            <h2>FarmFresh Email Verification</h2>
            <p>Your OTP is:</p>
            <h1 style='color:#2e7d32;'>$otp</h1>
            <p>This OTP is valid for <b>5 minutes</b>.</p>
            <p>Please do not share it with anyone.</p>
        </div>
    ";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "OTP sent to your email"
    ]);

} catch (Exception $e) {

    // User is registered but email failed
    echo json_encode([
        "success" => true,
        "message" => "Registered, but OTP email failed"
    ]);
}
