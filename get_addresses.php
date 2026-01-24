<?php
header("Content-Type: application/json");
include "db.php";

$user_id = $_GET['user_id'] ?? '';

if ($user_id == '') {
    echo json_encode([
        "success" => false,
        "message" => "User ID required"
    ]);
    exit;
}

$sql = "SELECT * FROM addresses WHERE user_id=? ORDER BY is_default DESC, id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$addresses = [];

while ($row = $result->fetch_assoc()) {
    $addresses[] = $row;
}

echo json_encode([
    "success" => true,
    "addresses" => $addresses
]);

$stmt->close();
$conn->close();
