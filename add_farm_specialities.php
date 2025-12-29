<?php
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);

include "db.php";

/* ================= INPUTS ================= */
$email = trim($_POST['email'] ?? '');
$specialities = trim($_POST['specialities'] ?? '');
// Example: Organic,Vegetables,Fruits

if ($email === '' || $specialities === '') {
    echo json_encode([
        "success" => false,
        "message" => "Missing data"
    ]);
    exit;
}

/* ================= FIND FARMER ================= */
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE email=? AND role='farmer'"
);
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

/* ================= CLEAR OLD SPECIALITIES ================= */
$delete = $conn->prepare(
    "DELETE FROM farm_specialities WHERE farmer_id=?"
);
$delete->bind_param("i", $farmer_id);
$delete->execute();

/* ================= SAVE NEW SPECIALITIES ================= */
$list = explode(",", $specialities);

$insert = $conn->prepare(
    "INSERT INTO farm_specialities (farmer_id, speciality)
     VALUES (?, ?)"
);

foreach ($list as $item) {
    $item = trim($item);
    if ($item === '') continue;

    $insert->bind_param("is", $farmer_id, $item);
    $insert->execute();
}

echo json_encode([
    "success" => true,
    "message" => "Specialities saved"
]);
?>
