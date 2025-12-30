<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

/* ================= VALIDATION ================= */
$product_id = $_POST['product_id'] ?? '';

if ($product_id === '' || !isset($_FILES['images'])) {
    echo json_encode([
        "success" => false,
        "message" => "Product ID or images missing"
    ]);
    exit;
}

/* ================= CREATE FOLDER ================= */
$uploadDir = "uploads/products/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

/* ================= UPLOAD IMAGES ================= */
$files = $_FILES['images'];
$total = count($files['name']);

for ($i = 0; $i < $total; $i++) {

    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }

    $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
    $fileName = uniqid("prod_") . "." . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($files['tmp_name'][$i], $targetPath)) {

        // ✅ FIXED: image_url (matches DB)
        $stmt = $conn->prepare(
            "INSERT INTO product_images (product_id, image_url)
             VALUES (?, ?)"
        );
        $stmt->bind_param("is", $product_id, $targetPath);
        $stmt->execute();
    }
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success" => true,
    "message" => "Images uploaded successfully"
]);
