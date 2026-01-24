<?php
header("Content-Type: application/json");

/* 🔧 Enable errors while testing (turn OFF in production) */
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

/* ================= RECEIVE USER ID ================= */
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "User ID required"
    ]);
    exit;
}

/* ================= FETCH USER + FARM ================= */
$sql = "
SELECT 
    u.id AS user_id,
    u.name AS user_name,
    u.email,
    f.farm_name,
    f.location,
    f.description AS farm_description
FROM users u
LEFT JOIN farms f ON f.user_id = u.id
WHERE u.id = ?
LIMIT 1
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query prepare failed"
    ]);
    exit;
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Profile not found"
    ]);
    exit;
}

$data = $result->fetch_assoc();

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "profile" => [
        "name"             => $data['user_name'],
        "email"            => $data['email'],
        "farm_name"        => $data['farm_name'] ?? "",
        "location"         => $data['location'] ?? "",
        "farm_description" => $data['farm_description'] ?? ""
    ]
]);
