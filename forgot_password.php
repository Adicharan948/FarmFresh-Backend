<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set("Asia/Kolkata");

include "db.php";

/* ================= PHPMailer ================= */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/phpmailer/src/Exception.php";
require __DIR__ . "/phpmailer/src/PHPMailer.php";
require __DIR__ . "/phpmailer/src/SMTP.php";

/* ================= INPUT ================= */
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$purpose = isset($_POST['purpose']) ? strtolower(trim($_POST['purpose'])) : ''; // signup | forgot

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $purpose === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email and purpose required"
    ]);
    exit;
}

if ($purpose !== 'signup' && $purpose !== 'forgot') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid purpose"
    ]);
    exit;
}

/* ================= CHECK USER ================= */
if ($purpose === "forgot") {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows !== 1) {
        echo json_encode([
            "success" => false,
            "message" => "Email not registered"
        ]);
        exit;
    }
}

/* ================= GENERATE OTP ================= */
$otp = strval(rand(100000, 999999));
$expires_at = date("Y-m-d H:i:s", strtotime("+5 minutes"));

/* ================= DELETE OLD OTP ================= */
$delete = $conn->prepare(
    "DELETE FROM email_otps WHERE email=? AND purpose=?"
);
$delete->bind_param("ss", $email, $purpose);
$delete->execute();

/* ================= INSERT OTP ================= */
$insert = $conn->prepare(
    "INSERT INTO email_otps (email, otp, purpose, expires_at)
     VALUES (?, ?, ?, ?)"
);

if (!$insert) {
    echo json_encode([
        "success" => false,
        "message" => "OTP insert prepare failed"
    ]);
    exit;
}

$insert->bind_param("ssss", $email, $otp, $purpose, $expires_at);
$insert->execute();

/* ================= SEND EMAIL ================= */
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "adicharan40@gmail.com";   // your Gmail
    $mail->Password = "vtsvlasfuluuzhww";        // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom("adicharan40@gmail.com", "FarmFresh");
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = "FarmFresh OTP Verification";
    $mail->Body = "
        <h2>Your OTP</h2>
        <h1 style='color:#6D28D9;'>$otp</h1>
        <p>This OTP is valid for <b>5 minutes</b>.</p>
        <p>If you didn’t request this, please ignore.</p>
    ";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "OTP sent to email"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Email sending failed"
    ]);
    exit;
}
