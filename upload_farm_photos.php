<?php
header("Content-Type: application/json");
error_reporting(0);
ini_set('display_errors', 0);

include "db.php";

/* ================= INPUT ================= */
$email = trim($_POST['email'] ?? '');

if ($email === '') {
    echo json_encode([
        "success" => false,
        "message" => "Email missing"
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

/* ================= UPLOAD DIRECTORY ================= */
$uploadDir = __DIR__ . "/uploads/farms/";
$dbPath    = "uploads/farms/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* ================= FILE CHECK ================= */
if (!isset($_FILES['images']) || empty($_FILES['images']['tmp_name'])) {
    echo json_encode([
        "success" => false,
        "message" => "No images received"
    ]);
    exit;
}

$uploaded = 0;

/* ================= UPLOAD MAX 5 IMAGES ================= */
foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {

    if ($uploaded >= 5) break;
    if (!is_uploaded_file($tmp)) continue;

    $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
    $fileName = uniqid("farm_", true) . "." . $ext;

    $serverPath = $uploadDir . $fileName;
    $dbImagePath = $dbPath . $fileName;

    if (move_uploaded_file($tmp, $serverPath)) {

        $insert = $conn->prepare(
            "INSERT INTO farm_photos (farmer_id, image_path)
             VALUES (?, ?)"
        );
        $insert->bind_param("is", $farmer_id, $dbImagePath);
        $insert->execute();

        $uploaded++;
    }
}

echo json_encode([
    "success"  => true,
    "message"  => "Photos uploaded",
    "uploaded" => $uploaded
]);
?>
