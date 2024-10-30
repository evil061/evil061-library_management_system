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

    $sql = "SELECT allotted_shift FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return " "; // Return a string indicating the ID is not found
    }

    return $row['allotted_shift'];
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
    <link rel="stylesheet" href="stylse.css">
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
  text-align:center;
}

th, td {
  border: 1px solid #ddd;
  padding: 8px;
  text-align: left;
}

th {
  background-color: #f2f2f2;
  text-align:center;
}

tr:nth-child(even) {

    
}

button[type="submit"] {
  background-color: #4CAF50;
  color: white;
  padding: 5px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button[type="submit"]:hover {
  background-color: grey;
  
}

.home-button-container {
  text-align: center;
  margin-top: 20px;
}

.home-button {
  background-color: #f0f0f0;
  color: #333;
  border: 1px solid #ccc;
  padding: 5px 10px;
  border-radius: 4px;
  cursor: pointer;
  text-decoration: none;
}
.linear{
    display:flex;
}
</style>
<body>
<form method="GET" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="text" name="search" placeholder="Search by name, ID, or email">
    <button type="submit">Search</button>
</form>
    <table>
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Email</th>
                <th>Alotted Shift </th>
                <th>Seat Name</th>
                <th>Fees</th>
                
                <th colspan= "3" > action </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <?php
                    // Retrieve the seat ID for the current student
                    $seatId = getSeatIdByStudentId($student['student_id']);
                    $shift_idd = getShiftIdByStudentId($student['student_id']);
                ?>
                <tr>
                    <td><?php echo $student['student_id']; ?></td>
                    <td><?php echo $student['name']; ?></td>
                    <td><?php echo $student['email']; ?></td>
                    
                    <td>
                    <div class="linear">    
                    <?php echo getShiftNameByShiftId($shift_idd); ?></td>
                    <td><?php  
                    
                    $seatId = getSeatIdByStudentId($student['student_id']);
                    echo getSeatNameBySeatId($seatId);
                    ?></td>
                    <td>
                    <form method="post" action="pay_fees.php?student_id=<?php echo $student['student_id']; ?>">
                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                    <button type="submit">pay</button>
                    </form>
                    </td>
                    <td>
                    <form method="post" action="edit_student.php?student_id=<?php echo $student['student_id']; ?>">
                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                    <button type="submit">Edit</button>
                </form>
            </td>
            <td>
            <button class="home-button" onclick="location.href='assign_seat_to_student.php?student_id=<?php echo $student['student_id'];?>'">SEATING</button>
            </td>
            <td>
                <form method="GET" action="delete_student.php">
                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                    <button class="home-button" onclick="location.href='delete_student.php?id=<?php echo $student['student_id']; ?>'">Delete</button>
                </form>
            </div></td>
           
                </tr>

            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="home-button-container">
    <button class="home-button" onclick="location.href='index.php'">Home</button>
    
  </div>

  
</body>
</html>