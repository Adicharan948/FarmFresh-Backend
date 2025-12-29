<?php
header("Content-Type: application/json");
include "db.php";

$product_id = $_POST['product_id'] ?? '';

if (!$product_id || empty($_FILES['images'])) {
    echo json_encode(["success"=>false,"message"=>"Missing data"]);
    exit;
}

$uploadDir = "uploads/products/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
    $name = time() . "_" . $_FILES['images']['name'][$i];
    $path = $uploadDir . $name;

    move_uploaded_file($tmp, $path);

    $stmt = $conn->prepare(
        "INSERT INTO product_images (product_id, image_path)
         VALUES (?, ?)"
    );
    $stmt->bind_param("is", $product_id, $path);
    $stmt->execute();
}

echo json_encode(["success"=>true,"message"=>"Images uploaded"]);
