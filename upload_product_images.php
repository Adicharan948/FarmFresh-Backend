<?php
header("Content-Type: application/json");

/* 🔒 Production safe */
error_reporting(0);
ini_set('display_errors', 0);

include "db.php";

/* ================= RECEIVE DATA ================= */
$product_id = (int)($_POST['product_id'] ?? 0);

if ($product_id <= 0 || empty($_FILES['images'])) {
    echo json_encode([
        "success" => false,
        "message" => "Product ID or images missing"
    ]);
    exit;
}

/* ================= VERIFY PRODUCT ================= */
$check = $conn->prepare(
    "SELECT id FROM products WHERE id = ? LIMIT 1"
);
$check->bind_param("i", $product_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows !== 1) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid product"
    ]);
    exit;
}

/* ================= CONFIG ================= */
$allowedExt = ['jpg', 'jpeg', 'png', 'webp'];
$maxSize    = 2 * 1024 * 1024; // 2MB
$maxImages  = 5;

/* ================= PATHS ================= */
$uploadDirAbsolute = __DIR__ . "/uploads/products/";
$uploadDirDB       = "uploads/products/";

if (!is_dir($uploadDirAbsolute)) {
    mkdir($uploadDirAbsolute, 0777, true);
}

/* ================= NORMALIZE FILE ARRAY ================= */
$files = $_FILES['images'];
$total = count($files['name']);
$stored = 0;

/* ================= UPLOAD ================= */
for ($i = 0; $i < $total && $stored < $maxImages; $i++) {

    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }

    if ($files['size'][$i] > $maxSize) {
        continue;
    }

    $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        continue;
    }

    $fileName  = uniqid("prod_", true) . "." . $ext;
    $targetAbs = $uploadDirAbsolute . $fileName;
    $targetDB  = $uploadDirDB . $fileName;

    if (move_uploaded_file($files['tmp_name'][$i], $targetAbs)) {

        $stmt = $conn->prepare(
            "INSERT INTO product_images (product_id, image_url)
             VALUES (?, ?)"
        );

        if ($stmt) {
            $stmt->bind_param("is", $product_id, $targetDB);
            if ($stmt->execute()) {
                $stored++;
            }
        }
    }
}

/* ================= FINAL RESPONSE ================= */
if ($stored === 0) {
    echo json_encode([
        "success" => false,
        "message" => "No images uploaded"
    ]);
    exit;
}

echo json_encode([
    "success" => true,
    "message" => $stored . " image(s) uploaded successfully"
]);
	