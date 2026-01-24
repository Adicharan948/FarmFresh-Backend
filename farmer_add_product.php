<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// 1. Force Error Reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db.php";

$response = [];

try {
    // 2. Data Reception
    $farmer_id   = (int)($_POST['farmer_id'] ?? 0);
    $category    = trim($_POST['category'] ?? '');
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price       = (float)($_POST['price'] ?? 0);
    $unit        = trim($_POST['unit'] ?? '');
    $quantity    = (int)($_POST['quantity'] ?? 0);

    if ($farmer_id <= 0 || empty($name)) {
        throw new Exception("Missing required fields. Farmer ID: $farmer_id, Name: $name");
    }

    // 3. Check 'farms' table
    $stmt = $conn->prepare("SELECT id FROM farms WHERE user_id = ? LIMIT 1");
    $stmt->bind_param("i", $farmer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("Farm not found for user_id $farmer_id. Check 'farms' table.");
    }
    
    $farm = $result->fetch_assoc();
    $farm_id = $farm['id'];
    $stmt->close();

    // 4. Insert into 'products'
    // Ensure these columns exist in your DB: farm_id, category, name, description, price, unit, quantity
    $query = "INSERT INTO products (farm_id, category, name, description, price, unit, quantity) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssdsi", $farm_id, $category, $name, $description, $price, $unit, $quantity);
    $stmt->execute();
    
    $response = [
        "success" => true, 
        "product_id" => $conn->insert_id, 
        "message" => "Product added!"
    ];

} catch (mysqli_sql_exception $e) {
    // This catches SQL errors like "Table doesn't exist" or "Unknown column"
    $response = ["success" => false, "message" => "SQL Error: " . $e->getMessage()];
} catch (Exception $e) {
    $response = ["success" => false, "message" => "Error: " . $e->getMessage()];
}

echo json_encode($response);
?>