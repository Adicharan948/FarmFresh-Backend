<?php
header("Content-Type: application/json");
date_default_timezone_set("Asia/Kolkata");

/* ✅ DEBUG MODE (turn OFF after testing) */
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
        "message" => "Email not found"
    ]);
    exit;
}

/* ================= UPDATE PASSWORD ================= */
$hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);

$update = $conn->prepare(
    "UPDATE users SET password = ? WHERE email = ?"
);

if (!$update) {
    echo json_encode([
        "success" => false,
        "message" => "Password update prepare failed"
    ]);
    exit;
}

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
