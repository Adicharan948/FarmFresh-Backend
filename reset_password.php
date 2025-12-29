<?php
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);
date_default_timezone_set("Asia/Kolkata");

include "db.php";

/* ================= RECEIVE DATA ================= */
$email        = trim($_POST['email'] ?? '');
$new_password = trim($_POST['new_password'] ?? '');

/* ================= VALIDATION ================= */
if ($email === '' || $new_password === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email and new password required"
    ]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

if (strlen($new_password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters"
    ]);
    exit;
}

/* ================= CHECK USER EXISTS ================= */
$user = $conn->prepare("SELECT id FROM users WHERE email=?");
$user->bind_param("s", $email);
$user->execute();
$user->store_result();

if ($user->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Email not found"
    ]);
    exit;
}

/* ================= UPDATE PASSWORD ================= */
$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

$update = $conn->prepare(
    "UPDATE users SET password=? WHERE email=?"
);
$update->bind_param("ss", $hashedPassword, $email);

if (!$update->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Password update failed"
    ]);
    exit;
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "Password reset successful"
]);
exit;
