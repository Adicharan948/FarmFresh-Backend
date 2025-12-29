<?php
header("Content-Type: application/json");
include "db.php";

$sql = "
SELECT p.*, 
       (SELECT image_path 
        FROM product_images 
        WHERE product_id=p.id 
        LIMIT 1) AS image
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
    "success"=>true,
    "products"=>$data
]);
