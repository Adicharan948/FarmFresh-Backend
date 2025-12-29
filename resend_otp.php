<?php
include "db.php";

$email = $_POST['email'];
$otp = rand(100000, 999999);

// update OTP
$conn->query("DELETE FROM otp_verification WHERE email='$email'");
$conn->query("INSERT INTO otp_verification (email, otp) VALUES ('$email','$otp')");

// try sending email (optional)
@mail($email, "FarmFresh OTP", "Your new OTP is: $otp");

echo json_encode([
    "success" => true,
    "message" => "OTP resent"
]);
