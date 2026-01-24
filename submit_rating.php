<?php
// --- 1. SMART DATABASE CONNECTION ---
$conn = null;
$config_files = ['db_config.php', 'config.php', 'db.php', 'connection.php', 'db_connect.php'];

foreach ($config_files as $file) {
    if (file_exists($file)) {
        include $file;
        break;
    }
}

// Fallback: Connect directly if no config file found
if (!$conn) {
    $conn = new mysqli("localhost", "root", "", "farmfresh_db");
}

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Database Connection Failed"]));
}

// --- 2. SUBMIT RATING LOGIC ---
$order_id = $_POST['order_id'];
$farmer_id = $_POST['farmer_id'];
$consumer_id = $_POST['consumer_id'];
$product_quality = $_POST['product_quality'];
$delivery_experience = $_POST['delivery_experience'];
$farmer_service = $_POST['farmer_service'];
$comment = $_POST['comment'] ?? '';

// Check for existing rating
$check = $conn->query("SELECT id FROM ratings WHERE order_id = '$order_id'");
if($check && $check->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Order already rated."]);
    exit;
}

$sql = "INSERT INTO ratings (order_id, farmer_id, consumer_id, product_quality, delivery_experience, farmer_service, comment) 
        VALUES ('$order_id', '$farmer_id', '$consumer_id', '$product_quality', '$delivery_experience', '$farmer_service', '$comment')";

if ($conn->query($sql) === TRUE) {
    $conn->query("UPDATE orders SET is_rated = 1 WHERE id = '$order_id'");
    echo json_encode(["success" => true, "message" => "Rating submitted successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Database Error: " . $conn->error]);
}

$conn->close();
?>