<?php
header("Content-Type: application/json");

/* 🔒 Production safe */
error_reporting(0);
ini_set('display_errors', 0);

include "db.php";

/* ================= RECEIVE DATA ================= */
$user_id = (int)($_POST['user_id'] ?? 0);
$specialities = trim($_POST['specialities'] ?? '');

if ($user_id <= 0 || $specialities === '') {
    echo json_encode([
        "success" => false,
        "message" => "Invalid data"
    ]);
    exit;
}

/* ================= GET FARM ID ================= */
/* ✅ FIX: user_id (NOT farmer_id) */
$farm = $conn->prepare(
    "SELECT id FROM farms WHERE user_id = ? LIMIT 1"
);
$farm->bind_param("i", $user_id);
$farm->execute();
$res = $farm->get_result();

if ($res->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Farm not found"
    ]);
    exit;
}

$farm_id = (int)$res->fetch_assoc()['id'];

/* ================= CLEAR OLD SPECIALITIES ================= */
$del = $conn->prepare(
    "DELETE FROM farm_specialities WHERE farm_id = ?"
);
$del->bind_param("i", $farm_id);
$del->execute();

/* ================= INSERT NEW SPECIALITIES ================= */
$insert = $conn->prepare(
    "INSERT INTO farm_specialities (farm_id, speciality)
     VALUES (?, ?)"
);

foreach (explode(",", $specialities) as $s) {
    $s = trim($s);
    if ($s === '') continue;

    $insert->bind_param("is", $farm_id, $s);
    $insert->execute();
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "Farm specialities saved successfully"
]);
