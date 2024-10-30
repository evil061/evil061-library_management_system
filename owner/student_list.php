<?php
session_start();

include '../includes/db_connect.php';

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}


function getLibraryIdByOwnerId($owner_id) {
    global $conn; // Assuming you have a global database connection named `$conn`

    $sql = "SELECT library_id FROM libraries WHERE owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return null;
    }

    return $row['library_id'];
}

function getSeatNameBySeatId($seatId) {
    global $conn;

    $sql = "SELECT seat_number  FROM seats WHERE seat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seatId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return " ";
    }

    return $row['seat_number'];
}

function getStudentsByLibraryId($library_id) {
    global $conn;

    $sql = "SELECT student_id, name, email FROM students WHERE library_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }

    return $students;
}

function getSeatIdByStudentId($student_id) {
    global $conn;

    $sql = "SELECT seat_id FROM seat_shifts WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return null; // or some default value
    }

    return $row['seat_id'];
}

function getShiftIdByStudentId($student_id) {
    global $conn;

    $sql = "SELECT shift_id FROM seat_shifts WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return " "; // Return a string indicating the ID is not found
    }

    return $row['shift_id'];
}

function getShiftNameByShiftId($shift_id) {
    global $conn;

    $sql = "SELECT shift_name FROM shifts WHERE shift_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shift_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return "N/A ";
    }

    return $row['shift_name'];
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $owner_id = $_SESSION['user_id'];
    $library_id = getLibraryIdByOwnerId($owner_id);
    $students = getStudentsByLibraryId($library_id);


}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student List</title>
    <link rel="stylesheet" href="style.css">
</head>
<style>
body{
    font-family: Arial, sans-serif;
  align-items: center;
  background: linear-gradient(to right, #f27121, #e94057);
}
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
  background-color: ;
}
</style>
<body>
    <h1>Student List</h1>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Seat Name</th>
                <th>Shift name  </th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <?php
                    // Retrieve the seat ID for the current student
                    $seatId = getSeatIdByStudentId($student['student_id']);
                ?>
                <tr>
                    <td><?php echo $student['student_id']; ?></td>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['email']; ?></td>
                    
                    <td><?php echo getSeatNameBySeatId($seatId); ?></td>
                    
                    <?php  
                    $shift_idd = getShiftIdByStudentId($student['student_id']);

                    ?>
                    <td><?php echo getShiftNameByShiftId($shift_idd); ?></td>


            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
</body>
</html>