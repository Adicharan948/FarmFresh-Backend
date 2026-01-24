<?php
header("Content-Type: application/json");
include "db.php";

$user_id   = $_POST['user_id'];
$type      = $_POST['type'];      // Home / Work
$name      = $_POST['name'];
$address   = $_POST['address'];
$phone     = $_POST['phone'];
$is_default = $_POST['is_default'] ?? 0;

/* If default, reset others */
if ($is_default == 1) {
    $conn->query("UPDATE addresses SET is_default=0 WHERE user_id=$user_id");
}

$stmt = $conn->prepare(
    "INSERT INTO addresses (user_id, type, full_name, address, phone, is_default)
     VALUES (?,?,?,?,?,?)"
);

$stmt->bind_param(
    "issssi",
    $user_id,
    $type,
    $name,
    $address,
    $phone,
    $is_default
);

$stmt->execute();

echo json_encode([
    "success" => true,
    "message" => "Address saved successfully"
]);
