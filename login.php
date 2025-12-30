<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

/* ================= RECEIVE DATA ================= */
$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email and password required"
    ]);
    exit;
}

/* ================= CHECK USER ================= */
$stmt = $conn->prepare(
    "SELECT id, password, role, is_verified
     FROM users
     WHERE email = ?
     LIMIT 1"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query prepare failed"
    ]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}

$user = $result->fetch_assoc();

/* ================= VERIFY PASSWORD ================= */
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}

/* ================= CHECK VERIFIED ================= */
if ((int)$user['is_verified'] !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Please verify your email first"
    ]);
    exit;
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "user_id" => (int)$user['id'],
    "role"    => $user['role'],
    "message" => "Login successful"
]);
