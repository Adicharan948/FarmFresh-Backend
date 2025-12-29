<?php
header("Content-Type: application/json");
include "db.php";

$farmer_id = intval($_GET['farmer_id'] ?? 0);

if ($farmer_id === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Farmer ID required"
    ]);
    exit;
}

$sql = "SELECT id, name, price, unit, quantity 
        FROM products 
        WHERE farmer_id = ?
        ORDER BY id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode([
    "success" => true,
    "products" => $products
]);
