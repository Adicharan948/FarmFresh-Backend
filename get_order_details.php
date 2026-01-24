<?php
header("Content-Type: application/json");
include "db.php";
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;
if (empty($order_id)) {
    echo json_encode([
        "success" => false,
        "message" => "Order ID is required"
    ]);
    exit;
}
// Get order details
// Joined with 'addresses' for real delivery address and phone
// Joined with 'users' for consumer name
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.order_number,
        o.total_amount,
        o.payment_method,
        o.delivery_date,
        o.delivery_slot,
        o.status,
        o.created_at,
        u.name as consumer_name,
        u.email as consumer_email,
        ad.phone as consumer_phone,
        ad.address as full_delivery_address
    FROM orders o
    LEFT JOIN users u ON o.consumer_id = u.id
    LEFT JOIN addresses ad ON o.address_id = ad.id
    WHERE o.id = ?
");
if ($stmt === false) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Order not found"]);
    exit;
}
$order = $result->fetch_assoc();
// Get order items with images
$stmt2 = $conn->prepare("
    SELECT 
        oi.product_id,
        oi.quantity,
        oi.price,
        p.name as product_name,
        (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = ?
");
$stmt2->bind_param("i", $order_id);
$stmt2->execute();
$items_result = $stmt2->get_result();
$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = [
        "product_id" => (int)$item['product_id'],
        "product_name" => $item['product_name'] ?? "Unknown Product",
        "quantity" => (int)$item['quantity'],
        "price" => $item['price'],
        "image_url" => $item['image_url'] ?? ""
    ];
}
echo json_encode([
    "success" => true,
    "order" => [
        "id" => (int)$order['id'],
        "order_number" => $order['order_number'],
        "consumer_name" => $order['consumer_name'] ?? "Unknown Customer",
        "consumer_phone" => $order['consumer_phone'] ?? $order['consumer_email'] ?? "",
        "delivery_date" => $order['delivery_date'],
        "delivery_slot" => $order['delivery_slot'],
        "delivery_address" => $order['full_delivery_address'] ?? "Address not found",
        "total_amount" => $order['total_amount'],
        "payment_method" => $order['payment_method'],
        "status" => ucfirst($order['status'] ?? "Pending"),
        "created_at" => $order['created_at'],
        "items" => $items
    ],
    "message" => "Order details fetched successfully"
]);
?>