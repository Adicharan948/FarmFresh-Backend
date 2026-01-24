<?php
header("Content-Type: application/json; charset=UTF-8");
include 'db.php'; 
$response = array();
if (isset($_GET['farmer_id'])) {
    $farmer_id = $_GET['farmer_id'];
    // ✅ REMOVED 'u.profile_image' because it caused an error. 
    // We selected 'u.name' only.
    $query = "SELECT r.id, r.product_quality, r.delivery_experience, r.farmer_service, r.comment, u.name as reviewer_name 
              FROM ratings r 
              JOIN users u ON r.consumer_id = u.id 
              WHERE r.farmer_id = '$farmer_id' 
              ORDER BY r.id DESC";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        $response['success'] = false;
        $response['message'] = "Database Error: " . mysqli_error($conn);
        echo json_encode($response);
        exit;
    }
    if (mysqli_num_rows($result) > 0) {
        $response['success'] = true;
        $response['ratings'] = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $avg_rating = ($row['product_quality'] + $row['delivery_experience'] + $row['farmer_service']) / 3;
            
            $review = array();
            $review['reviewerName'] = $row['reviewer_name'];
            $review['rating'] = round($avg_rating, 1);
            $review['comment'] = $row['comment'];
            // Send empty string or default URL since column is missing
            $review['reviewerImage'] = ""; 
            array_push($response['ratings'], $review);
        }
        $response['message'] = "Ratings fetched successfully";
    } else {
        $response['success'] = true;
        $response['ratings'] = array();
        $response['message'] = "No ratings found";
    }
} else {
    $response['success'] = false;
    $response['message'] = "Required field 'farmer_id' is missing";
}
echo json_encode($response);
?>