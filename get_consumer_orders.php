<?php
header("Content-Type: application/json; charset=UTF-8");
include 'db.php'; 
$response = array();
if (isset($_GET['consumer_id'])) {
    $consumer_id = $_GET['consumer_id'];
    $query = "SELECT * FROM orders WHERE consumer_id = '$consumer_id' ORDER BY id DESC";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $response['success'] = true;
        $response['orders'] = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $order = array();
            $order['id'] = $row['id'];
            $order['order_number'] = $row['order_number'];
            $order['date'] = $row['created_at']; 
            $order['total_amount'] = $row['total_amount'];
            $order['status'] = $row['status'];
            $order['delivery_date'] = $row['delivery_date'];
            $order['delivery_slot'] = $row['delivery_slot'];
            $order['farmer_id'] = $row['farmer_id']; 
            $order['consumer_id'] = $row['consumer_id'];
            $order['is_rated'] = 0; // Default
            // 1. Fetch Farm Name
            if(isset($row['farmer_id'])){
                 $fid = $row['farmer_id'];
                 $f_query = "SELECT farm_name FROM users WHERE id='$fid'";
                 $f_res = mysqli_query($conn, $f_query);
                 if($f_res && $f_row = mysqli_fetch_assoc($f_res)){
                     $order['farm_name'] = $f_row['farm_name'];
                 } else {
                     $order['farm_name'] = "Farm #$fid";
                 }
            } else {
                $order['farm_name'] = "Unknown Farm";
            }
            // 2. ✅ Fetch Real Address
            $aid = $row['address_id'];
            if (!empty($aid)) {
                $a_query = "SELECT * FROM addresses WHERE id='$aid'";
                $a_res = mysqli_query($conn, $a_query);
                if ($a_res && $a_row = mysqli_fetch_assoc($a_res)) {
                    // Format: "John Doe, 123 Street, City. Ph: 987..."
                    $order['delivery_address'] = $a_row['full_name'] . "\n" . $a_row['address'] . "\nPh: " . $a_row['phone'];
                } else {
                    $order['delivery_address'] = "Address Details Not Found (ID: $aid)";
                }
            } else {
                $order['delivery_address'] = "No Address Selected";
            }
            
            // 3. Fetch Real Items
            $oid = $row['id'];
            $items_query = "SELECT p.name, oi.quantity FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = '$oid'";
            $items_res = mysqli_query($conn, $items_query);
            
            $item_names = array();
            if ($items_res) {
                while($i_row = mysqli_fetch_assoc($items_res)) {
                    $qty = $i_row['quantity'];
                    $name = $i_row['name'];
                    $item_names[] = $name . " (" . $qty . ")";
                }
            }
            
            if (!empty($item_names)) {
                $order['items'] = implode(", ", $item_names);
            } else {
                $order['items'] = "No items found";
            }
            array_push($response['orders'], $order);
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Database Error: " . mysqli_error($conn);
    }
} else {
    $response['success'] = false;
    $response['message'] = "Missing consumer_id";
}
echo json_encode($response);
?>