<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db.php";
/* ================= SQL QUERY ================= */
// Joined with 'farms' to get the farmer's User ID (farmer_id)
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
        SELECT pi.image_url
        FROM product_images pi
        WHERE pi.product_id = p.id
        LIMIT 1
    ) AS image_url
FROM products p
LEFT JOIN farms f ON p.farm_id = f.id
WHERE p.quantity > 0
ORDER BY p.created_at DESC
";
/* ================= EXECUTE QUERY ================= */
$result = $conn->query($sql);
if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $conn->error
    ]);
    exit;
}
/* ================= FETCH DATA ================= */
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        "id" => (int)$row['id'],
        "name" => $row['name'],
        "price" => $row['price'],
        "unit" => $row['unit'],
        "quantity" => $row['quantity'],
        "category" => $row['category'],
        "description" => $row['description'],
        "farmer_id" => (int)$row['farmer_id'],
        "image_url" => $row['image_url']
    ];
}
/* ================= RESPONSE ================= */
echo json_encode([
    "success"  => true,
    "products" => $products
]);
?>