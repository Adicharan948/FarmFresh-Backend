<?php
header("Content-Type: application/json");
include "db.php";

$email = trim($_POST['email'] ?? '');
$location = trim($_POST['location'] ?? '');

if ($email === '' || $location === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email and location required"
    ]);
    exit;
}

$stmt = $conn->prepare("UPDATE users SET location=? WHERE email=?");
$stmt->bind_param("ss", $location, $email);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Location saved"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to save location"
    ]);
}
?>
