<?php
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);

include "db.php";

/* ================= INPUTS ================= */
$email = trim($_POST['email'] ?? '');
$farm_name = trim($_POST['farm_name'] ?? '');
$location = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');

/* ================= VALIDATION ================= */
if ($email === '' || $farm_name === '' || $location === '') {
    echo json_encode([
        "success" => false,
        "message" => "Required fields missing"
    ]);
    exit;
}

/* ================= FIND FARMER ================= */
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE email=? AND role='farmer'"
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
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Farmer not found"
    ]);
    exit;
}

$farmer_id = $res->fetch_assoc()['id'];

/* ================= CHECK FARM ================= */
$check = $conn->prepare(
    "SELECT id FROM farms WHERE farmer_id=?"
);
$check->bind_param("i", $farmer_id);
$check->execute();
$exists = $check->get_result()->num_rows > 0;

/* ================= INSERT / UPDATE ================= */
if ($exists) {
    $query = $conn->prepare(
        "UPDATE farms
         SET farm_name=?, location=?, description=?
         WHERE farmer_id=?"
    );
    $query->bind_param("sssi", $farm_name, $location, $description, $farmer_id);
} else {
    $query = $conn->prepare(
        "INSERT INTO farms (farmer_id, farm_name, location, description)
         VALUES (?, ?, ?, ?)"
    );
    $query->bind_param("isss", $farmer_id, $farm_name, $location, $description);
}

if (!$query->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to save farm details"
    ]);
    exit;
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "Farm details saved"
]);
