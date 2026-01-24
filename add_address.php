<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

/* ================= RECEIVE ================= */
$user_id    = $_POST['user_id'] ?? '';
$type       = $_POST['type'] ?? '';
$full_name  = $_POST['name'] ?? '';   // coming as "name" from Android
$phone      = $_POST['phone'] ?? '';
$address    = $_POST['address'] ?? '';
$is_default = $_POST['is_default'] ?? 0;

/* ================= VALIDATE ================= */
if (
    $user_id === '' ||
    $type === '' ||
    $full_name === '' ||
    $phone === '' ||
    $address === ''
) {
    echo json_encode([
        "success" => false,
        "message" => "Missing fields",
        "received" => $_POST
    ]);
    exit;
}

/* ================= SQL ================= */
$sql = "INSERT INTO addresses 
        (user_id, type, full_name, phone, address, is_default)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Prepare failed",
        "error" => $conn->error
    ]);
    exit;
}

/* ================= BIND ================= */
$stmt->bind_param(
    "issssi",
    $user_id,
    $type,
    $full_name,
    $phone,
    $address,
    $is_default
);

/* ================= EXECUTE ================= */
if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "address_id" => $stmt->insert_id
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Insert failed",
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
