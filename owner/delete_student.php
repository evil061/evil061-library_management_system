
<?php
session_start();

include '../includes/db_connect.php';

$student_id = $_GET['student_id'];

// Confirm deletion
if (isset($_POST['confirm'])) {
    // Delete the student
    $sql = "DELETE FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    if ($stmt->execute()) {
        // Update student ID to null in seatshifts table
        $sql = "UPDATE seat_shifts SET is_occupied = NULL, student_id = NULL WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        if ($stmt->execute()) {
            // Delete from student_fees table
            $sql = "DELETE FROM student_fees WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            if ($stmt->execute()) {
                header('Location: student_management.php'); // Redirect to student list
                exit;
            } else {
                // Error deleting student
                echo "Error deleting from fees"; // Replace with proper error handling
            }
        } else {
            // Error updating seatshifts table
            echo "Error updating seatshifts table"; // Replace with proper error handling
        }
    } else {
        // Error deleting student
        echo "Error deleting form students"; // Replace with proper error handling
    }
} else {
    // Display confirmation form
    ?>
<style>
    /* Add some basic styling to the form */
    form {
        max-width: 300px;
        margin: 40px auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    /* Style the paragraph */
    p {
        margin-bottom: 20px;
    }

    /* Style the submit button */
    input[type="submit"] {
        background-color: #4CAF50;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    /* Style the cancel link */
    a {
        text-decoration: none;
        color: #337ab7;
    }

    a:hover {
        color: #23527c;
    }
</style>

<form method="post">
    <p>Are you sure you want to delete student with ID <?php echo $student_id; ?>?</p>
    <input type="submit" name="confirm" value="Yes, delete student">
    <a href="student_management.php">No, cancel</a>
</form>
    <?php
}
?>