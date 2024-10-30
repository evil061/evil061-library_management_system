<?php
include '../includes/db_connect.php';

// Get the fee ID from the form submission
$fee_id = $_POST['fee_id'];

// Update the payment status and date in the database
$sql = "UPDATE student_fees SET payment_status = 'paid', payment_date = NOW() WHERE fee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fee_id);
$stmt->execute();

if ($stmt->affected_rows == 1) {
    echo "Payment successful!";
} else {
    echo "Payment failed.";
}
?>