<?php
header("Content-Type: application/json");
include "db.php";

$product_id = (int)($_GET['id'] ?? 0);
if ($product_id <= 0) {
    // Try POST if not in GET (though Android typically sends GET/POST consistently, checking both is safe or stick to one). 
    // Android delete usually uses query param or body. Retrofit Call<ApiResponse> deleteProduct(@Query("id") int id); usually.
    // Let's check API service if I can. But standard Delete is usually GET or DELETE method.
    // Assuming GET or POST with 'id'.
    // Actually, let's look at ApiService if needed. But usually ID is enough.
    $product_id = (int)($_POST['id'] ?? 0);
}

if ($product_id <= 0) {
    echo json_encode(["success" => false, "message" => "Product ID required"]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
         echo json_encode(["success" => true, "message" => "Product deleted successfully"]);
    } else {
         echo json_encode(["success" => false, "message" => "Product not found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete product"]);
}
?>
