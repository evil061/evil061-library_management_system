<?php
session_start();

include '../includes/db_connect.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

$student_id = $_SESSION["user_id"];


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
    $sql = "SELECT allotted_shift FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['allotted_shift'];
}


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $student_id = $_SESSION['user_id'];
    $allotted_shift_id = getStudentAllottedShiftId($student_id);

    $library_id = getLibraryIdByOwnerId($student_id);
    $seats = getSeatsByLibraryId($library_id);

    // Iterate over each seat and get its shifts
    $shiftsBySeat = [];
    foreach ($seats as $seat_id) {
        $shifts = getShiftBySeatId($seat_id);
        $shiftsBySeat[$seat_id] = $shifts;
    }

    // Filter shifts based on the student's allotted shift
    $filteredShiftsBySeat = [];
    foreach ($shiftsBySeat as $seat_id => $shifts) {
        $filteredShifts = array_filter($shifts, function ($shift) use ($allotted_shift_id) {
            return $shift['shift_id'] === $allotted_shift_id;
        });
        if (!empty($filteredShifts)) {
            $filteredShiftsBySeat[$seat_id] = $filteredShifts;
        }
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
      margin: 20px;
      align-items: center;
      min-height: 100vh;
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
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }

    .home-button-occupied {
        background-color: red;
        color: #333;
        border: 1px solid #ccc;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }

    .book-button {
        background-color: green; /* Add this for the book button */
        color: #fff;
        border: 1px solid #ccc;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
    }

    .release-button {
        background-color: blue; /* Add this for the release button */
        color: #fff;
        border: 1px solid #ccc;
        padding: 10px 20px;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
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
                <th>Book</th>
                
            </tr>
        </thead>
        <tbody>
    <?php foreach ($filteredShiftsBySeat as $seat_id => $shifts): ?>
        <?php foreach ($shifts as $shift): ?>
            <tr>
                <td><?php echo getSeatNameBySeatId($seat_id); ?></td>
                <td><?php echo $shift['shift_name']; ?></td>
                <td><?php echo $shift['is_occupied'] ? 'Yes' : 'No'; ?></td>
                <td><?php echo $shift['student_id']; ?></td>
                <td><?php echo getStudentNameByStudentId($shift['student_id']); ?></td>
                
                <td>
    <input type="hidden" name="book" value="<?php echo $shift['student_id']; ?>">
    
    <?php if ($shift['is_occupied']): ?>
        <?php if ($shift['student_id'] == $student_id): ?>
            <button class="release-button" onclick="location.href='release.php?seat_id=<?php echo $seat_id; ?>&shift_id=<?php echo $shift['shift_id']; ?>'">Release</button>
        <?php endif; ?>
        <button class="home-button-occupied" disabled>Occupied</button>
    <?php else: ?>
        <button class="book-button" onclick="location.href='book.php?seat_id=<?php echo $seat_id; ?>&shift_id=<?php echo $shift['shift_id']; ?>&shift_start=<?php echo $shift['start_time'];?>&shift_end=<?php echo $shift['end_time'];?>'">Book</button>
    <?php endif; ?>
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