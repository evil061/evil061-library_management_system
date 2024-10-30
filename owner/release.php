<?php
session_start();

include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $seat_id = $_GET['seat_id'];
    $shift_id = $_GET['shift_id'];

    // Get the library ID of the seat
    $seat_library_id = getSeatLibraryId($seat_id);

    // Check if the seat belongs to the library owned by the current user
    $library_owner_id = $_SESSION['user_id'];
    if (!isLibraryOwner($seat_library_id, $library_owner_id)) {
        echo '<script>
            alert("You cannot release a seat that does not belong to your library.");
            window.location.href = "seating_arrangement.php";
        </script>';
        exit();
    }

    // Release the shift
    releaseShift($seat_id, $shift_id);

    // Redirect back to the shifts page
    header('Location: seating_arrangement.php');
    exit();
}

function releaseShift($seat_id, $shift_id) {
    global $conn;

   
// Check for overlapping shifts
$shifts = getShiftBySeatId($seat_id);

// Get the start and end times of the shift that you're trying to assign
$sql = "SELECT start_time, end_time FROM shifts WHERE shift_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $shift_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$shift_start = $row['start_time'];
$shift_end = $row['end_time'];

foreach ($shifts as $existing_shift) {
    if (overlap($shift_start, $shift_end, $existing_shift['start_time'], $existing_shift['end_time'])) {
        // Update the overlapping shift to set is_occupied to 1
        $sql = "UPDATE seat_shifts SET is_occupied = 0 WHERE seat_id = ? AND shift_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $seat_id, $existing_shift['shift_id']);
        $stmt->execute();
    }
}

// Update the seat_shifts table to set the student ID and is_occupied to 1
$sql = "UPDATE seat_shifts SET student_id = NULL, is_occupied = 0 WHERE seat_id = ? AND shift_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $seat_id, $shift_id);
$stmt->execute();

    if ($stmt->affected_rows == 1) {
        echo '<script>
            alert("Seat removed successfully to the student.");
            window.location.href = "student_management.php";
        </script>';
    } else {
        echo '<script>
            alert("Failed to remove seat to the student.");
            window.location.href = "student_management.php";
        </script>';
    }
}



// Assuming you have the overlap function defined as you provided
function overlap($start1, $end1, $start2, $end2) {
    // Convert times to Unix timestamps
    $start1 = strtotime($start1);
    $end1 = strtotime($end1);
    $start2 = strtotime($start2);
    $end2 = strtotime($end2);

    // Handle midnight transition
    if ($end1 < $start1) {
        $end1 += 86400; // Add 24 hours (seconds in a day)
    }
    if ($end2 < $start2) {
        $end2 += 86400;
    }

    // Check for overlap
    return ($start1 < $end2 && $end1 > $start2);
}

function getShiftTimes($shift_id) {
    global $conn;
    $sql = "SELECT start_time, end_time FROM shifts WHERE shift_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shift_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

function getSeatLibraryId($seat_id) {
    global $conn;
    $sql = "SELECT library_id FROM seats WHERE seat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seat_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['library_id'];
}

function isLibraryOwner($library_id, $owner_id) {
    global $conn;
    $sql = "SELECT * FROM libraries WHERE library_id = ? AND owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $library_id, $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}
function getShiftBySeatId($seat_id) {
    global $conn;
    $sql = "SELECT shifts.*, seat_shifts.seat_id, seat_shifts.is_occupied, seat_shifts.student_id FROM shifts JOIN seat_shifts ON shifts.shift_id = seat_shifts.shift_id WHERE seat_shifts.seat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seat_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Process the result and return an array of shifts with seat numbers
    $shifts = [];
    while ($row = $result->fetch_assoc()) {
        $shifts[] = [
            'shift_id' => $row['shift_id'],
            'shift_name' => $row['shift_name'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'seat_id' => $row['seat_id'],
            'is_occupied' => $row['is_occupied'],
            'student_id' => $row['student_id']
        ];
    }

    return $shifts;
}