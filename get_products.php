<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

$sql = "
SELECT 
    p.id,
    p.name,
    p.price,
    p.unit,
    p.quantity,
    p.category,
    p.description,
    (
        SELECT image_url
        FROM product_images
        WHERE product_id = p.id
        LIMIT 1
    ) AS image_url
FROM products p
WHERE p.quantity > 0
ORDER BY p.created_at DESC
";

$res = $conn->query($sql);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success"  => true,
    "products" => $data
]);
