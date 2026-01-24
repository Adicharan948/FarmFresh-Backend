<?php
header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

/* ✅ TEMP: SHOW ERRORS FOR DEBUG */
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
    echo json_encode(["success"=>false,"message"=>"All fields are required"]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success"=>false,"message"=>"Invalid email address"]);
    exit;
}

/* ================= CHECK EMAIL ================= */
$check = $conn->prepare("SELECT id FROM users WHERE email=?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    echo json_encode(["success"=>false,"message"=>"Email already registered"]);
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
    echo json_encode(["success"=>false,"message"=>"User insert failed"]);
    exit;
}

/* ================= OTP ================= */
$otp = strval(rand(100000, 999999));

$conn->query("DELETE FROM otp_verification WHERE email='$email'");

$insertOtp = $conn->prepare(
    "INSERT INTO otp_verification (email, otp, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))"
);
$insertOtp->bind_param("ss", $email, $otp);

if (!$insertOtp->execute()) {
    echo json_encode(["success"=>false,"message"=>"OTP insert failed"]);
    exit;
}

/* ================= SEND EMAIL ================= */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "adicharan40@gmail.com";
    $mail->Password = "vtsvlasfuluuzhww";
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    $mail->setFrom("adicharan40@gmail.com", "FarmFresh");
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = "FarmFresh OTP Verification";
    $mail->Body = "<h2>Your OTP is <b>$otp</b></h2><p>Valid for 5 minutes</p>";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "OTP sent to your email"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Email failed: ".$mail->ErrorInfo
    ]);
}
