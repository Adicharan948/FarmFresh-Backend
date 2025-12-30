<?php
include "db.php";

$user_id = $_POST['user_id'] ?? 0;
$farm_name = $_POST['farm_name'] ?? '';
$location = $_POST['location'] ?? '';
$description = $_POST['description'] ?? '';

if ($user_id == 0) {
    echo json_encode(["success"=>false,"message"=>"Invalid user"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO farms (farmer_id, farm_name, location, description)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE
     farm_name=?, location=?, description=?"
);

$stmt->bind_param(
    "issssss",
    $user_id, $farm_name, $location, $description,
    $farm_name, $location, $description
);

$stmt->execute();

echo json_encode(["success"=>true]);
