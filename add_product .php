<?php
header("Content-Type: application/json");
include "db.php";

$farmer_id  = $_POST['farmer_id'] ?? '';
$category   = $_POST['category'] ?? '';
$name       = $_POST['name'] ?? '';
$description= $_POST['description'] ?? '';
$price      = $_POST['price'] ?? '';
$unit       = $_POST['unit'] ?? '';
$quantity   = $_POST['quantity'] ?? '';
$tags       = $_POST['tags'] ?? '';

if (!$farmer_id || !$name || !$price || !$quantity) {
    echo json_encode(["success"=>false,"message"=>"Missing fields"]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO products
     (farmer_id, category, name, description, price, unit, quantity, tags)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "isssdiss",
    $farmer_id,
    $category,
    $name,
    $description,
    $price,
    $unit,
    $quantity,
    $tags
);

if ($stmt->execute()) {
    echo json_encode([
        "success"=>true,
        "product_id"=>$stmt->insert_id
    ]);
} else {
    echo json_encode(["success"=>false,"message"=>"Insert failed"]);
}
