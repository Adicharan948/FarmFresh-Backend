<?php
header("Content-Type: application/json");
include "db.php";

$order_id = $_POST['order_id'];
$status = $_POST['status'];

if (empty($order_id) || empty($status)) {
    echo json_encode([
        "success" => false,
        "message" => "Order ID and status are required"
    ]);
    exit;
}

// Validate status
$allowed_statuses = ['Confirmed', 'Cancelled', 'Delivered', 'Pending', 'New', 'Packed', 'Out for Delivery'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid status"
    ]);
    exit;
}

// Update order status
// FIX: Ensure status column is VARCHAR(50) to support new statuses (Delivered, Out for Delivery etc)
// This fixes issues if the DB column was created as a restricted ENUM
$conn->query("ALTER TABLE orders MODIFY COLUMN status VARCHAR(50)");

$stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");

if ($stmt === false) {
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("si", $status, $order_id);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Order status updated successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to update order status: " . $stmt->error
    ]);
}
?>