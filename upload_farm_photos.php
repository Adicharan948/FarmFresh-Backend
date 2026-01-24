<?php
header("Content-Type: application/json");

/* 🔒 Production safe */
error_reporting(0);
ini_set('display_errors', 0);

include "db.php";

/* ================= RECEIVE DATA ================= */
$user_id = (int)($_POST['user_id'] ?? 0);

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid user"
    ]);
    exit;
}

if (!isset($_FILES['images'])) {
    echo json_encode([
        "success" => false,
        "message" => "No images received"
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

/* ================= UPLOAD PHOTOS ================= */
$uploadDir = "uploads/farms/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
    if ($tmp === '') continue;

    $fileName = time() . "_" . basename($_FILES['images']['name'][$i]);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($tmp, $targetPath)) {
        $stmt = $conn->prepare(
            "INSERT INTO farm_photos (farm_id, image_path)
             VALUES (?, ?)"
        );
        $stmt->bind_param("is", $farm_id, $fileName);
        $stmt->execute();
    }
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "Farm photos uploaded successfully"
]);
