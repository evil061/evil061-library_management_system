<?php
// Include the necessary PHP file for database connection and functions
include '../includes/db_connect.php';

// Get the student's ID from the session or URL parameter
$student_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_GET['user_id'];

// Check if the student ID is valid
if (empty($student_id)) {
    echo "Invalid student ID.";
    exit();
}

// Get the student's fees from the database
$sql = "SELECT * FROM student_fees WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$fees = [];
while ($row = $result->fetch_assoc()) {
    // Calculate the remaining balance and due date
    $remaining_balance = $row['fee_amount'] - $row['amount_paid'];
    $due_date = $row['due_date'];
    $fees[] = [
        'fee_id' => $row['fee_id'],
        'fee_amount' => $row['fee_amount'],
        'amount_paid' => $row['amount_paid'],
        'remaining_balance' => $remaining_balance,
        'due_date' => $due_date,
        'payment_status' => $row['payment_status'],
    ];
}

// Pass the fees array to the HTML template
include 'student_fees.php';