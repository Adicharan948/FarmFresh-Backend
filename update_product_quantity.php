<?php
header("Content-Type: application/json");
include "db.php";

$product_id = $_POST['product_id'] ?? '';
$qty = $_POST['quantity'] ?? '';

if (!$product_id || !$qty) {
    echo json_encode(["success"=>false]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE products 
     SET quantity = quantity - ? 
     WHERE id = ? AND quantity >= ?"
);

$stmt->bind_param("iii", $qty, $product_id, $qty);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    echo json_encode(["success"=>true]);
} else {
    echo json_encode([
        "success"=>false,
        "message"=>"Out of stock"
    ]);
}
