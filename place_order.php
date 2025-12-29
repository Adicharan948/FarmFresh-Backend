<?php
include "db.php";

$consumer_id = $_POST['consumer_id'];
$farmer_id = $_POST['farmer_id'];
$total = $_POST['total'];
$date = $_POST['delivery_date'];
$slot = $_POST['delivery_slot'];

$conn->query("INSERT INTO orders
(consumer_id,farmer_id,total_amount,status,delivery_date,delivery_slot)
VALUES ('$consumer_id','$farmer_id','$total','Pending','$date','$slot')");

echo json_encode(["success"=>true]);
?>
