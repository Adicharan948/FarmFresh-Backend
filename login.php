<?php
header("Content-Type: application/json");
include "db.php";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$res = $conn->query("SELECT * FROM users WHERE email='$email'");

if ($res->num_rows == 1) {
    $user = $res->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        echo json_encode(["success"=>true,"user"=>$user]);
    } else {
        echo json_encode(["success"=>false,"message"=>"Wrong password"]);
    }
} else {
    echo json_encode(["success"=>false,"message"=>"User not found"]);
}
?>
