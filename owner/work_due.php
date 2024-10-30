<?php
include '../includes/db_connect.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query to get students with due dates within the next 5 days
$sql = "SELECT s.name, sf.fee_id, sf.due_date 
        FROM students s 
        JOIN student_fees sf ON s.student_id = sf.student_id 
        WHERE sf.due_date BETWEEN DATE_SUB(CURDATE(), INTERVAL 5 DAY) AND DATE_ADD(CURDATE(), INTERVAL 5 DAY) 
        ";

$result = $conn->query($sql);

if (!$result) {
    die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
    echo "<h2>Students with Due Dates within the Next 5 Days</h2>";
    echo "<table border='1'>";
    echo "<tr><th>Student Name</th><th>Fee ID</th><th>Due Date</th></tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["name"] . "</td>";
        echo "<td>" . $row["fee_id"] . "</td>";
        echo "<td>" . $row["due_date"] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<p>No students with due dates within the next 5 days.</p>";
}


// Get the student's registration date
$sql = "SELECT joining_date FROM students WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$registration_date = $row['registration_date'];

// Create a new entry in the students_fees table
$due_date = date('Y-m-t', strtotime('+1 month', strtotime($registration_date)));
$payment_status = 'pending';
$amount_paid = 0;
$fee_amount = 0; // You can set a default fee amount or calculate it based on the student's details

$sql = "INSERT INTO student_fees (student_id, amount_paid, payment_status, due_date, fee_amount) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $student_id, $amount_paid, $payment_status, $due_date, $fee_amount);
$stmt->execute();

$conn->close();
?>