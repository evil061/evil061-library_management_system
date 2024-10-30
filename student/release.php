<?php
session_start();

include '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page
    header('Location: login.php');
    exit();
}

$student_id = $_SESSION["user_id"];


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $seat_id = $_GET['seat_id'];
    $shift_id = $_GET['shift_id'];

    // Get the library ID of the student
    $library_id = getLibraryIdByOwnerId($student_id);

    // Check if the student owns the seat
    if (!isSeatOwnedByStudent($seat_id, $student_id)) {
        //echo "You cannot release a seat that you do not own.";
        echo '<script>
        alert("You cannot release a seat that you do not own.");
        window.location.href = "seat_management.php";
    </script>';
        exit();
    }

    // Get the student ID of the seat owner
    $seat_owner_id = getSeatOwnerId($seat_id, $shift_id);

    // Check if the student who is trying to release the seat is the same student who owns the seat
    if ($seat_owner_id !== $student_id) {
        //echo "You cannot release a seat that is owned by another student.";
        echo '<script>
        alert("You cannot release a seat  owned by another student.");
        window.location.href = "seat_management.php";
    </script>';
        exit();
    }

    // Release the shift
    releaseShift($seat_id, $shift_id);

    // Redirect back to the shifts page
    header('Location: seat_management.php');
    exit();
}

function releaseShift($seat_id, $shift_id) {
    global $conn;

    // Update the seat_shifts table to set is_occupied to 0 and student_id to NULL
    $sql = "UPDATE seat_shifts SET is_occupied = 0, student_id = NULL WHERE seat_id = ? AND shift_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $seat_id, $shift_id);
    $stmt->execute();
}

function getLibraryIdByOwnerId($owner_id) {
    global $conn;
    $sql = "SELECT library_id FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['library_id'];
}

function isSeatOwnedByStudent($seat_id, $student_id) {
    global $conn;
    $sql = "SELECT * FROM seat_shifts WHERE seat_id = ? AND student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $seat_id, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

function getSeatOwnerId($seat_id, $shift_id) {
    global $conn;
    $sql = "SELECT student_id FROM seat_shifts WHERE seat_id = ? AND shift_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $seat_id, $shift_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['student_id'];
}