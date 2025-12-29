<?php
header("Content-Type: application/json");
include "db.php";

$user_id = $_GET['user_id'] ?? '';

if ($user_id=='') {
    echo json_encode(["success"=>false,"message"=>"User ID required"]);
    exit;
}

$stmt = $conn->prepare(
    "SELECT farm_name, location, description
     FROM farms WHERE user_id=?"
);
$stmt->bind_param("i",$user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["success"=>false,"message"=>"No farm found"]);
    exit;
}

echo json_encode([
    "success"=>true,
    "data"=>$result->fetch_assoc()
]);
