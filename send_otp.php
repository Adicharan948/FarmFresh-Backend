<?php
header("Content-Type: application/json");

/* 🔥 ENABLE ERRORS TEMPORARILY */
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

/* ================= GENERATE OTP ================= */
$otp = (string) rand(100000, 999999);

/* ================= DELETE OLD OTP ================= */
$del = $conn->prepare("DELETE FROM otp_verification WHERE email=?");
$del->bind_param("s", $email);
$del->execute();

/* ================= INSERT OTP ================= */
$stmt = $conn->prepare(
    "INSERT INTO otp_verification (email, otp, expires_at)
     VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 5 MINUTE))"
);
$stmt->bind_param("ss", $email, $otp);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "OTP DB insert failed"
    ]);
    exit;
}

/* ================= SEND EMAIL ================= */
$mail = new PHPMailer(true);

try {
    /* 🔥 SHOW SMTP ERROR */
    $mail->SMTPDebug = 2; // IMPORTANT
    $mail->Debugoutput = 'error_log';

    $mail->isSMTP();
    $mail->Host       = "smtp.gmail.com";
    $mail->SMTPAuth   = true;
    $mail->Username   = "adicharan40@gmail.com";
    $mail->Password   = "vtsvlasfuluuzhww"; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    $mail->CharSet = "UTF-8";

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

    /* 🔴 RETURN REAL ERROR */
    echo json_encode([
        "success" => false,
        "message" => "Mailer Error: " . $mail->ErrorInfo
    ]);
}
