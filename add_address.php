<?php
include "db.php";

$user_id = $_POST['user_id'];
$address = $_POST['address'];
$type = $_POST['type'];

$conn->query("INSERT INTO addresses (user_id,address,type)
VALUES ('$user_id','$address','$type')");

echo json_encode(["success"=>true]);
?>
