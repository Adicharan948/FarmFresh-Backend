<?php
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);
date_default_timezone_set("Asia/Kolkata");
include "db.php";
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$role     = trim($_POST['role'] ?? '');
if ($email === '' || $password === '' || $role === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email, password and role required"
    ]);
    exit;
}
$stmt = $conn->prepare(
    "SELECT id, name, password, role, is_verified
     FROM users
     WHERE email = ?
     LIMIT 1"
);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database error"
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
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password"
    ]);
    exit;
}
if ($role !== $user['role']) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid role selected"
    ]);
    exit;
}
if ((int)$user['is_verified'] !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Please verify your email first"
    ]);
    exit;
}
echo json_encode([
    "success" => true,
    "user_id" => (int)$user['id'],
    "name"    => $user['name'],
    "role"    => $user['role'],
    "message" => "Login successful"
]);
?>