<?php
include "db.php";

$user_id = $_POST['user_id'];

foreach ($_FILES['images']['tmp_name'] as $i => $tmp) {
    $name = time()."_".$_FILES['images']['name'][$i];
    move_uploaded_file($tmp, "uploads/farms/".$name);

    $stmt = $conn->prepare(
        "INSERT INTO farm_photos (farm_id, image_path)
         VALUES (?, ?)"
    );
    $stmt->bind_param("is", $user_id, $name);
    $stmt->execute();
}

echo json_encode(["success"=>true]);
