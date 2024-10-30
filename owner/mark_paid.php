<?php
// Include the database connection file
include '../includes/db_connect.php';

// Get the student ID and updated information from the form submission
    $fee_id = $_GET["fee_id"];
    $amount= $_GET["amount"];
    $student_id=$_GET['student_id'];
    //update status to paid 
    $status= "paid";
    $sql = "UPDATE student_fees SET  payment_status= ?, amount_paid=? WHERE fee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $status,$amount, $fee_id);
    $stmt->execute();

    // select purpose
    $sql = "SELECT purpose FROM student_fees WHERE fee_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $fee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $purpose = $row['purpose'];
    
// Check if the student ID exists in the students table
$check_student_sql = "SELECT * FROM students WHERE student_id = '$student_id'";
$check_student_result = $conn->query($check_student_sql);
echo $check_student_result->num_rows;
echo $purpose;
if ($check_student_result->num_rows > 0 and $purpose=== "Library_fees" ) {

    // Get the student's registration date
$sql = "SELECT joining_date FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$registration_date = $row['joining_date'];
echo  $registration_date;
// get selected months
$sql = "SELECT month FROM student_fees WHERE fee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $fee_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$selectedMonth = $row['month'];

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

}
    
    echo '<script>
            alert("Amount Submitted successfully");
            window.location.href = "pay_fees.php?student_id='.$student_id.'";
        </script>';
    ?>