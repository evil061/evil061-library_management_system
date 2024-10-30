<?php
session_start();

include '../includes/db_connect.php';

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

if (isset($_GET['seat_id']) && isset($_GET['shift_id']) && isset($_GET['student_id'])) {
    $seat_id = $_GET['seat_id'];
    $shift_id = $_GET['shift_id'];
    $student_id = $_GET['student_id'];

    if (empty($student_id)) {
        echo '<script>
            alert("Student id not found please reselect student ");
            window.location.href = "student_management.php";
        </script>';
        exit();
    }
}

if (isset($_GET['seat_id']) && isset($_GET['shift_id']) && isset($_GET['student_id'])) {
    $seat_id = $_GET['seat_id'];
    $shift_id = $_GET['shift_id'];
    $student_id = $_GET['student_id'];

    // Get the student's maximum bookings
    $sql = "SELECT max_bookings FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $max_bookings = $row['max_bookings'];

    // Get the student's current bookings
    $sql = "SELECT COUNT(*) as current_bookings FROM seat_shifts WHERE student_id = ? AND is_occupied = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $current_bookings = $row['current_bookings'];

    // Check if the student has exceeded their maximum bookings
    if ($current_bookings >= $max_bookings) {
        echo '<script>
            alert("Student has exceeded their maximum bookings.");
            window.location.href = "student_management.php";
        </script>';
        exit();
    }

    // Check if the seat is already occupied for the selected shift
    $sql = "SELECT * FROM seat_shifts WHERE seat_id = ? AND shift_id = ? AND is_occupied = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $seat_id, $shift_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo '<script>
            alert("Seat is already occupied for this shift.");
            window.location.href = "student_management.php";
        </script>';
        exit();
    }


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
        $sql = "UPDATE seat_shifts SET is_occupied = 1 WHERE seat_id = ? AND shift_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $seat_id, $existing_shift['shift_id']);
        $stmt->execute();
    }
}

// Update the seat_shifts table to set the student ID and is_occupied to 1
$sql = "UPDATE seat_shifts SET student_id = ?, is_occupied = 1 WHERE seat_id = ? AND shift_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $student_id, $seat_id, $shift_id);
$stmt->execute();

    if ($stmt->affected_rows == 1) {
        echo '<script>
            alert("Seat assigned successfully to the student.");
            window.location.href = "student_management.php";
        </script>';
    } else {
        echo '<script>
            alert("Failed to assign seat to the student.");
            window.location.href = "student_management.php";
        </script>';
    }
} else {
    echo "Invalid request.";
}

// Function to get shifts by seat ID
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

// Function to check if two time ranges overlap
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
    if ($start1 < $end2 && $end1 > $start2) {
        return true;
    }
    return false;
}