<?php
include "db.php";

$user_id = $_GET['user_id'];

$res = $conn->query("SELECT * FROM orders WHERE consumer_id='$user_id' OR farmer_id='$user_id'");
$data=[];

while($row=$res->fetch_assoc()){
    $data[]=$row;
}

echo json_encode($data);
?>
