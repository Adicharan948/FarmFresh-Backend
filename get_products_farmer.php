<?php
header("Content-Type: application/json");
include "db.php";

$farmer_id = $_GET['farmer_id'] ?? '';

if (!$farmer_id) {
    echo json_encode(["success"=>false]);
    exit;
}

$sql = "
SELECT p.*,
       (SELECT image_path FROM product_images 
        WHERE product_id=p.id LIMIT 1) AS image
FROM products p
WHERE p.farmer_id = ?
ORDER BY p.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode([
    "success"=>true,
    "products"=>$data
]);
