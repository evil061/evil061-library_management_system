<?php
// Include the database connection file
include '../includes/db_connect.php';

// Get the student ID and updated information from the form submission
$student_id = $_POST['student_id'];
$name = $_POST['name'];
$email = $_POST['email'];
$max_bookings = $_POST['max_bookings'];
$password = $_POST['password'];
$allotted_shift = (int)$_POST['allotted_shift'];
$due_date = $_POST['due_date'];

// Hash the password using a strong hashing algorithm (e.g., bcrypt)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Update the student information in the database
$sql = "UPDATE students SET name = ?, email = ?, max_bookings = ?, password = ? WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssisi", $name, $email, $max_bookings, $hashed_password, $student_id);
$stmt->execute();

// Check if the update was successful
if ($stmt->affected_rows > 0) {
    //echo "Student information updated successfully.";
    echo '<script>
        alert("Student information updated successfully.");
        window.location.href = "student_management.php";
    </script>';
} else {
    //echo "Error: Unable to update student information.";
    echo '<script>
        alert("Error: Unable to update student information.");
        window.location.href = "student_management.php";
    </script>';
}

// Check if the student exists in the student_fees table
$sql = "SELECT COUNT(*) FROM student_fees WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$stmt->store_result(); // Add this line
$stmt->bind_result($student_exists);
$stmt->fetch();

// If the student doesn't exist, create a new record in student_fees
/*if ($student_exists === 0) {
    $stmt->close(); // Add this line to close the previous statement
    $sql = "INSERT INTO student_fees (student_id, due_date) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $student_id, $due_date);
    $stmt->execute();
} else {
    // The student exists, so update the due date
    $stmt->close(); // Add this line to close the previous statement
    $sql = "UPDATE student_fees SET due_date = ? WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $due_date, $student_id);
    $stmt->execute();
}*/

// Update allotted_shift
$sql = "UPDATE students SET allotted_shift = ? WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $allotted_shift, $student_id);
$stmt->execute();

// Redirect back to the student list page
//header('Location: student_list.php');
exit;