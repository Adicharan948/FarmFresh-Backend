<?php
header("Content-Type: application/json");
include "db.php";

$user_id     = $_POST['user_id'] ?? '';
$farm_name   = trim($_POST['farm_name'] ?? '');
$location    = trim($_POST['location'] ?? '');
$description = trim($_POST['description'] ?? '');

if ($user_id=='' || $farm_name=='' || $location=='') {
    echo json_encode(["success"=>false,"message"=>"All fields required"]);
    exit;
}

/* check if farm already exists */
$check = $conn->prepare("SELECT id FROM farms WHERE user_id=?");
$check->bind_param("i",$user_id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    // UPDATE
    $stmt = $conn->prepare(
        "UPDATE farms SET farm_name=?, location=?, description=? WHERE user_id=?"
    );
    $stmt->bind_param("sssi",$farm_name,$location,$description,$user_id);
} else {
    // INSERT
    $stmt = $conn->prepare(
        "INSERT INTO farms (user_id,farm_name,location,description)
         VALUES (?,?,?,?)"
    );
    $stmt->bind_param("isss",$user_id,$farm_name,$location,$description);
}

$stmt->execute();

echo json_encode(["success"=>true,"message"=>"Farm profile saved"]);
