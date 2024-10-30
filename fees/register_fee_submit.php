<?php
    // Connect to the database
    include '../includes/db_connect.php';
    $student_id = $_POST['student_id'];
    $amount_paid = $_POST['amount_paid'];
    $month = $_POST['month'];
    $purpose = "Registration";
    $due_date = date('Y-m-d', strtotime('+1 month')); // Add one month from now
    $payment_status = $_POST['payment_status'];
    echo "$student_id, $purpose, $month, $amount_paid, $payment_status";
    // Insert the registration fee into the database
    $sql = "INSERT INTO student_fees (student_id, purpose, month, amount_paid, payment_status, due_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $student_id, $purpose, $month, $amount_paid, $payment_status, $due_date);
    $stmt->execute();

    // Check if the insertion was successful
    if ($stmt->affected_rows > 0) {
        echo "Registration fee submitted successfully!";
        header("Location: ../owner/assign_seat_to_student.php?student_id=$student_id");
        exit;
    } else {
        echo "Error submitting registration fee: " . $conn->error;
    }

    // Close the database connection
    $conn->close();
?>