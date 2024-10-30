<?php
session_start();
include '../includes/db_connect.php';

// Ensure the user is logged in as a library owner
// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}


$library_owner_id = $_SESSION['user_id'];

// Get the library ID associated with the owner
$sql = "SELECT library_id FROM owners WHERE owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $library_owner_id);
$stmt->execute();
$result = $stmt->get_result();

function getStudentName($studentId) {
    global $conn;

    $sql = "SELECT name FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    } else {
        return " "; // Handle case where student ID doesn't exist
    }
}

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $library_id = $row['library_id'];

    // Get all seats and their shifts
    $sql = "SELECT s.seat_id, s.seat_number, ls.start_time, ls.end_time, ss.is_occupied, ls.shift_name, ss.student_id
FROM seats s
LEFT JOIN seat_shifts ss ON s.seat_id = ss.seat_id
LEFT JOIN shifts ls ON ss.shift_id = ls.shift_id

WHERE s.library_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $shifts = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $shifts[] = $row;
        }
    }

} else {
    $shifts = array(); // Handle case where library owner is not found
}

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Seat Reservations - Owner Panel</title>
    <style>
        table {
  border-collapse: collapse;
  width: 100%;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

th {
  background-color: #f2f2f2;
}

tr:nth-child(even) {
  background-color: #f2f2f2;
}
    </style>
</head>
<body>
    <h1>Library Seat Reservations</h1>

    <table>
        <thead>
            <tr>
                <th>Seat ID</th>
                <th>Seat Number</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Status</th>
                <th>Shift Name</th>
                <th>Student ID</th>
                <th>Student Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (!empty($shifts)) {
                foreach ($shifts as $shift) {
                    echo "<tr>";
                    echo "<td>" . $shift['seat_id'] . "</td>";
                    echo "<td>" . $shift['seat_number'] . "</td>";
                    echo "<td>" . $shift['start_time'] . "</td>";
                    echo "<td>" . $shift['end_time'] . "</td>";
                    echo "<td>" . ($shift['is_occupied'] ? "Occupied" : "Available") . "</td>";
                    echo "<td>" . $shift['shift_name'] . "</td>";
                    echo "<td>" . ($shift['student_id'] ?? " ") . "</td>"; // Handle case where student is not reserved
                    echo "<td>" . getStudentName($shift['student_id']) . "</td>"; // Handle case where student is not reserved
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No reservations found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>