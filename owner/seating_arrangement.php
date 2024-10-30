<?php
session_start();

include '../includes/db_connect.php';
// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION["user_id"];


function getLibraryIdByOwnerId($owner_id) {
    global $conn;
    $sql = "SELECT library_id FROM owners WHERE owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['library_id'];
}

function getSeatsByLibraryId($library_id){
    global $conn;
    $sql = "SELECT seat_id FROM seats WHERE library_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Process the result and return an array of seat IDs
    $seatIds = [];
    while ($row = $result->fetch_assoc()) {
        $seatIds[] = $row['seat_id'];
    }

    return $seatIds;
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


function getStudentNameByStudentId($studentId) {
    global $conn;

    $sql = "SELECT name FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return " ";
    }

    return $row['name'];
}

function getStudentEmailByStudentId($studentId) {
    global $conn;

    $sql = "SELECT email FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return " ";
    }

    return $row['email'];
}

function getShiftsByLibraryId($library_id) {
    global $conn;
    $sql = "SELECT shifts.*, seat_shifts.seat_id, seat_shifts.is_occupied, seat_shifts.student_id FROM shifts JOIN seat_shifts ON shifts.shift_id = seat_shifts.shift_id WHERE seat_shifts.library_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Process the result and return an array of shifts with seat numbers
    $shifts = [];
    while ($row = $result->fetch_assoc()) {
        $shifts[$row['shift_id']] = [
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

function getSeatNameBySeatId($seatId) {
    global $conn;

    $sql = "SELECT seat_number  FROM seats WHERE seat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seatId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return "Seat not found";
    }

    return $row['seat_number'];
}

function getStudentAllottedShiftId($studentId) {
    global $conn;
    $sql = "SELECT allotted_shift FROM students WHERE student_id = ? AND is_occupied = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['allotted_shift'];
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $library_id = getLibraryIdByOwnerId($owner_id);
    $seats = getSeatsByLibraryId($library_id);
    

    // Iterate over each seat and get its shifts
    $shiftsBySeat = [];
    foreach ($seats as $seat_id) {
        $shifts = getShiftBySeatId($seat_id);
        $shiftsBySeat[$seat_id] = $shifts;

        foreach ($shifts as $shift) {
            $studentName = "";
            if (isset($shift['student_id']) && !empty($shift['student_id'])) {
                $studentName = getStudentNameByStudentId($shift['student_id']);
            }       
    }
    // Include the shifts.php template
    //include 'shifts.php';
}
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>View Shifts</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        align-items: center;
        background: linear-gradient(to right, #f27121, #e94057);
    }

    table {
      border-collapse: collapse; /* Ensures borders don't overlap */
      width: 100%; /* Makes the table full width */
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left; /* Aligns content to the left */
    }

    th {
      background-color: #f0f0f0; /* Light gray background for headers */
      font-weight: bold; /* Makes headers stand out */
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .home-button-container {
      text-align: center;
      margin-top: 20px;
    }

    .home-button {
      background-color: #f0f0f0;
      color: #333;
      border: 1px solid #ccc;
      padding: 10px 10px;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
    }
    input[type="text"],
select {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}
.occupied {
    background-color: red; /* Gray color for occupied seats */
    color: #666; /* Dark gray text color for occupied seats */
    cursor: not-allowed; /* Disable cursor for occupied seats */
}

.available {
    background-color: lightgreen; /* Light gray background for available seats */
    color: #333; /* Dark text color for available seats */
    cursor: pointer; /* Enable cursor for available seats */
}
  </style>
<body>
    <h1>Student list </h1>
    <table>
        <thead>
            <tr>

                <th>seat name</td>
                <th>Shift Name</th>
                <th>Occupied</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Email </th>
                <th>Book</th>
                <th>Empty</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($seats as $seat_id): ?>
                <?php foreach ($shiftsBySeat[$seat_id] as $shift): ?>
                    <tr>

                        <td><?php echo getSeatNameBySeatId($seat_id);?></td>
                        <td><?php echo $shift['shift_name']; ?></td>
                       
                        <td><?php echo $shift['is_occupied'] ? 'Yes' : 'No'; ?></td>
                        <td><?php echo $shift['student_id']; ?></td>
                        <td><?php echo getStudentNameByStudentId($shift['student_id']); ?></td>
                        <td><?php echo getStudentEmailByStudentId($shift['student_id']); ?></td>
                        <td>
                        <form action="book.php" method="GET">
                            <input type="text" name="student_id" id="student_id" placeholder="Student Id"  <?php echo $shift['is_occupied'] ? 'disabled' : ''; ?> required/>
                            <input type="hidden" name="seat_id" id="seat_id" value="<?php echo $seat_id; ?>">
                            <input type="hidden" name="shift_id" id="shift_id" value="<?php echo $shift['shift_id']; ?>">
                            <input type="hidden" name="start_time" id="start_time" value="<?php echo $shift['start_time'];?>">
                            <input type="hidden" name="end_time" id="end_time" value="<?php echo $shift['end_time'];?>">
                            <button class="home-button <?php echo $shift['is_occupied'] ? 'occupied' : 'available'; ?>">
                            <?php echo $shift['is_occupied'] ? 'Engaged' : 'Allocate'; ?>
                            </button></td>
                        </form>
                            <td>
                            <button class="home-button" onclick="location.href='release.php?seat_id=<?php echo $seat_id; ?>&shift_id=<?php echo $shift['shift_id']; ?>'">Release</button>
                            </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="home-button-container">
    <button class="home-button" onclick="location.href='index.php'">Home</button>
  </div>
</body>
</html>