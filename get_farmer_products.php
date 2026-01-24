<?php
header("Content-Type: application/json");
include "db.php";

$farmer_id = (int)($_GET['farmer_id'] ?? 0);

if ($farmer_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Farmer ID required",
        "products" => []
    ]);
    exit;
}

$sql = "
SELECT 
    p.id,
    p.name,
    p.price,
    p.unit,
    p.quantity,
    p.category,
    p.description,
    f.user_id AS farmer_id,
    (
        SELECT image_url 
        FROM product_images 
        WHERE product_id = p.id 
        LIMIT 1
    ) AS image_url
FROM products p
JOIN farms f ON p.farm_id = f.id
WHERE f.user_id = ?
ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => $conn->error]);
    exit;
}

$stmt->bind_param("i", $farmer_id);
$stmt->execute();

$result = $stmt->get_result();
$products = [];

while ($row = $result->fetch_assoc()) {
    $products[] = [
        "id" => (int)$row['id'],
        "name" => $row['name'],
        "price" => $row['price'],
        "unit" => $row['unit'],
        "quantity" => $row['quantity'],
        "category" => $row['category'],
        "description" => $row['description'] ?? "",
        "farmer_id" => (int)$row['farmer_id'],
        "image_url" => $row['image_url']
    ];
}

echo json_encode([
    "success" => true,
    "products" => $products
]);
?>