<?php
// Include the database connection file
include '../includes/db_connect.php';

// Get the student ID and updated information from the form submission
$student_id = $_POST["student_id"];
$amount_paid = $_POST["amount_paid"];
$payment_status = $_POST["payment_status"];
$due_date = $_POST["due_date"];
// Get the selected month from the form
$selectedMonth = $_POST['month'];
$purpose = $_POST["purpose"];
$remark = $_POST["remark"];
echo $purpose;
// Check if the student ID exists in the students table
$check_student_sql = "SELECT * FROM students WHERE student_id = '$student_id'";
$check_student_result = $conn->query($check_student_sql);

if ($check_student_result->num_rows > 0 and $purpose=== "Library_fee" ) {

    // Get the student's registration date
$sql = "SELECT joining_date FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$registration_date = $row['joining_date'];

// Create a new entry in the students_fees table
$new_due_date = date('Y-m-t', strtotime('+1 month', strtotime($registration_date)));
$new_payment_status = 'pending';
$new_amount_paid=0;
$new_purpose='Library_fees';
$fee_amount = 0; // You can set a default fee amount or calculate it based on the student's details
$months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
$index = array_search($selectedMonth, $months);
$new_selectedMonth = $months[($index + 1) % 12];

$sql = "INSERT INTO student_fees (student_id, amount_paid, payment_status, due_date, fee_amount, purpose,month) VALUES (?, ?, ?, ?, ?, ?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssss", $student_id, $new_amount_paid, $new_payment_status, $new_due_date, $fee_amount, $new_purpose,$new_selectedMonth);
$stmt->execute();
    // Student ID exists, insert fees record into the student_fees table
    $sql = "INSERT INTO student_fees (student_id, amount_paid, payment_status, due_date, month, purpose, remark) VALUES ('$student_id', '$amount_paid', '$payment_status', '$due_date', '$selectedMonth', '$purpose', '$remark')";
    
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to the student management page with a success message
        echo '<script>
            alert("Fees record added successfully.");
            window.location.href = "pay_fees.php?student_id='.$student_id.'";
        </script>';
    } else {
        // Handle error and redirect with an error message
        echo '<script>
            alert("Error: Unable to add new fees record.");
            window.location.href = "pay_fees.php?student_id='.$student_id.'";
        </script>';
    }

} else {
    // Insert fees record into the student_fees table
    $sql = "INSERT INTO student_fees (student_id, amount_paid, payment_status, due_date, month, purpose, remark) VALUES ('$student_id', '$amount_paid', '$payment_status', '$due_date', '$selectedMonth', '$purpose', '$remark')";
    if ($conn->query($sql) === TRUE) {
        // Redirect to the student management page with a success message
        echo '<script>
            alert("New student and fees record added successfully.");
            window.location.href = "pay_fees.php?student_id='.$student_id.'";
        </script>';
    } else {
        // Handle error and redirect with an error message
        echo '<script>
            alert("Amount Submitted successfully");
            window.location.href = "student_management.php";
        </script>';
    }

}
?>