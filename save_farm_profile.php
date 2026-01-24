<?php
header("Content-Type: application/json");

/* 🔒 Production safe */
error_reporting(0);
ini_set('display_errors', 0);

include "db.php";

/* ================= RECEIVE DATA ================= */
$user_id     = intval($_POST['user_id'] ?? 0);
$farm_name   = trim($_POST['farm_name'] ?? '');
$location    = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');

/* ================= VALIDATION ================= */
if ($user_id <= 0 || $farm_name === '' || $location === '') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid farm data"
    ]);
    exit;
}

/* ================= INSERT / UPDATE ================= */
$stmt = $conn->prepare(
    "INSERT INTO farms (user_id, farm_name, location, description)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
        farm_name = VALUES(farm_name),
        location = VALUES(location),
        description = VALUES(description)"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Database prepare failed"
    ]);
    exit;
}

$stmt->bind_param(
    "isss",
    $user_id,
    $farm_name,
    $location,
    $description
);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to save farm profile"
    ]);
    exit;
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "Farm profile saved successfully"
]);
