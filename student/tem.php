<?php
session_start();
include '../includes/db_connect.php';

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$student_id= $_SESSION['user_id'];
$current_student_id = $student_id; // Replace with the actual current student ID

// Query to get students with due dates within the next 5 days for the current student
$sql = "SELECT s.name, sf.fee_id, sf.due_date , sf.purpose
FROM students s 
JOIN student_fees sf ON s.student_id = sf.student_id 
WHERE sf.student_id = $current_student_id AND sf.due_date <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) AND sf.payment_status != 'paid'
";

$result = $conn->query($sql);

if (!$result) {
die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
echo "<h2>Pending Dues </h2>";
echo "<ul>";

while($row = $result->fetch_assoc()) {
echo "<li>";
echo "Name: " . $row["name"] . "<br>";
echo "Fee ID: " . $row["fee_id"] . "<br>";
echo "Due Date: " . $row["due_date"] . "<br>";
echo "Purpose: " . $row["purpose"] . "<br><br>";
echo "</li>";
}

echo "</ul>";
}

$conn->close();
?>