<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

/* ================= VALIDATION ================= */
$farmer_id   = $_POST['farmer_id']   ?? '';
$category    = $_POST['category']    ?? '';
$name        = $_POST['name']        ?? '';
$description = $_POST['description'] ?? '';
$price       = $_POST['price']       ?? '';
$unit        = $_POST['unit']        ?? '';
$quantity    = $_POST['quantity']    ?? '';

if (
    $farmer_id === '' || $category === '' || $name === '' ||
    $price === '' || $unit === '' || $quantity === ''
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

/* ================= INSERT PRODUCT ================= */
$stmt = $conn->prepare(
    "INSERT INTO products
     (farmer_id, category, name, description, price, unit, quantity)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Query prepare failed"
    ]);
    exit;
}

$stmt->bind_param(
    "isssdsi",
    $farmer_id,
    $category,
    $name,
    $description,
    $price,
    $unit,
    $quantity
);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Product insert failed"
    ]);
    exit;
}

/* ================= SUCCESS ================= */
echo json_encode([
    "success"    => true,
    "product_id"=> $stmt->insert_id,
    "message"   => "Product added successfully"
]);
