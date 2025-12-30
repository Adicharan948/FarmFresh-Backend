<?php
include "db.php";

$user_id = $_POST['user_id'];
$specialities = explode(",", $_POST['specialities']);

foreach ($specialities as $s) {
    $stmt = $conn->prepare(
        "INSERT INTO farm_specialities (farm_id, speciality)
         VALUES (?, ?)"
    );
    $stmt->bind_param("is", $user_id, $s);
    $stmt->execute();
}

echo json_encode(["success"=>true]);
