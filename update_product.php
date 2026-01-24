<?php
header("Content-Type: application/json");
include "db.php";

$id = $_POST['product_id'] ?? 0;
$name = $_POST['name'] ?? '';
$description = $_POST['description'] ?? '';
$price = $_POST['price'] ?? '';
$unit = $_POST['unit'] ?? '';
$quantity = $_POST['quantity'] ?? '';
$category = $_POST['category'] ?? '';

if (empty($id) || empty($name) || empty($price) || empty($quantity)) {
    echo json_encode(["success" => false, "message" => "Required fields missing"]);
    exit;
}

$stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, unit = ?, quantity = ?, category = ? WHERE id = ?");
$stmt->bind_param("ssssssi", $name, $description, $price, $unit, $quantity, $category, $id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Product updated successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to update product: " . $conn->error]);
}
?>
