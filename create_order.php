<?php
header("Content-Type: application/json; charset=UTF-8");
include 'db.php'; 
$response = array();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get input data
    $consumer_id = $_POST['consumer_id'] ?? '';
    $farmer_id = $_POST['farmer_id'] ?? 0; 
    $total_amount = $_POST['total_amount'] ?? '0';
    $payment_method = $_POST['payment_method'] ?? 'COD'; 
    $delivery_date = $_POST['delivery_date'] ?? '';
    $delivery_slot = $_POST['delivery_slot'] ?? '';
    $address_id = $_POST['address_id'] ?? 0;
    
    $items_json = $_POST['items'] ?? '[]';
    if (empty($consumer_id) || empty($address_id) || empty($items_json)) {
        $response['success'] = false;
        $response['message'] = "Missing required fields";
        echo json_encode($response);
        exit;
    }
    // Insert Order
    $query = "INSERT INTO orders (consumer_id, farmer_id, total_amount, payment_method, delivery_date, delivery_slot, address_id, status) 
              VALUES ('$consumer_id', '$farmer_id', '$total_amount', '$payment_method', '$delivery_date', '$delivery_slot', '$address_id', 'Pending')";
    if (mysqli_query($conn, $query)) {
        $order_id = mysqli_insert_id($conn);
        $order_number = "FF" . time() . rand(100, 999);
        mysqli_query($conn, "UPDATE orders SET order_number='$order_number' WHERE id='$order_id'");
        // --- PROCESS ITEMS ---
        $items = json_decode($items_json, true);
        if (is_array($items)) {
            foreach ($items as $item) {
                $pid = $item['product_id'];
                $qty = $item['quantity'];
                $price = $item['price'];
                // 1. Stock Reduction
                $update_stock = "UPDATE products SET quantity = quantity - $qty WHERE id = '$pid' AND quantity >= $qty";
                mysqli_query($conn, $update_stock);
                
                // 2. ✅ INSERT INTO order_items
                $insert_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ('$order_id', '$pid', '$qty', '$price')";
                mysqli_query($conn, $insert_item);
            }
        }
        $response['success'] = true;
        $response['message'] = "Order placed successfully";
        $response['order_number'] = $order_number;
        $response['order_id'] = $order_id;
    } else {
        $response['success'] = false;
        $response['message'] = "Database Error: " . mysqli_error($conn);
    }
} else {
    $response['success'] = false;
    $response['message'] = "Invalid Request Method";
}
echo json_encode($response);
?>