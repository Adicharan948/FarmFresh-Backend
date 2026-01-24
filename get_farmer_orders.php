<?php
header("Content-Type: application/json");
include "db.php";

$farmer_id = $_GET['farmer_id'];

if (empty($farmer_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Farmer ID is required"
    ]);
    exit;
}

// Get orders for this farmer
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.order_number,
        o.created_at as order_date,
        o.total_amount,
        o.status,
        o.delivery_date,
        o.delivery_slot,
        u.name as customer_name,
        GROUP_CONCAT(CONCAT(oi.quantity, 'x ', p.name) SEPARATOR ', ') as item_summary
    FROM orders o
    LEFT JOIN users u ON o.consumer_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE o.farmer_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $farmer_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = [
        "id" => $row['id'],
        "order_number" => $row['order_number'],
        "order_date" => $row['order_date'],
        "customer_name" => $row['customer_name'] ?? "Unknown Customer",
        "item_summary" => $row['item_summary'] ?? "No items",
        "total_amount" => $row['total_amount'],
        "status" => $row['status'] ?? "New",
        "delivery_date" => $row['delivery_date'],
        "delivery_slot" => $row['delivery_slot']
    ];
}

echo json_encode([
    "success" => true,
    "orders" => $orders,
    "message" => count($orders) > 0 ? "Orders fetched successfully" : "No orders found"
]);
?>