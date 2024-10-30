<?php
session_start();

include '../includes/db_connect.php';

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}
$owner_id=$_SESSION['user_id'];

function getLibraryIdByOwnerId($owner_id) {
    global $conn;
    $sql = "SELECT library_id FROM libraries WHERE owner_id = ?";
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_shift'])) {
        // ... (rest of the code for adding a shift remains the same)
    } elseif (isset($_POST['delete_all_shifts'])) {
        // ... (rest of the code for deleting all shifts remains the same)
    } elseif (isset($_POST['delete_shift'])) {
        // Delete the shift from seat_shifts and shifts
        $shiftId = $_POST['shift_id'];
        $sql = "DELETE FROM seat_shifts WHERE shift_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $shiftId);
        $stmt->execute();

        $sql = "DELETE FROM shifts WHERE shift_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $shiftId);
        $stmt->execute();

        echo "Shift deleted successfully!";
    } elseif (isset($_POST['edit_shift'])) {
        $shiftId = $_POST['shift_id'];
        $newShiftName = $_POST['new_shift_name'];

        // Update the shift name in the shifts table
        $sql = "UPDATE shifts SET shift_name = ? WHERE shift_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newShiftName, $shiftId);
        $stmt->execute();

        echo "Shift updated successfully!";
    }
}

// Retrieve all shifts for the library
$library_id = getLibraryIdByOwnerId($owner_id);
$sql = "SELECT shift_id, shift_name, start_time, end_time FROM shifts WHERE library_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $library_id);
$stmt->execute();
$result = $stmt->get_result();
$shifts = $result->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Add/Delete Shifts</title>
    <style>

body {
  font-family: Arial, sans-serif;
  margin: 0;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(to right, #f27121, #e94057);
}
input[type="text"]{
    padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

.container {
  flex: 1;
  padding: 20px;
}

.shift-list {
  width: 100%;
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th, td {
  padding: 12px 15px;
  text-align: left;
  border-bottom: 1px solid #ddd;
}

th {
  background-color: #f2f2f2;
}
.home-button-container {
    text-align: center;
    margin-top: 20px;
}

.home-button {
    text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            margin: 5px;
            
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px; 
}
</style>
</head>
<body>
    <h1 align="center">Manage Shifts</h1>
    <table>
        <thead>
            <tr>
                <th>Shift Name</th>
                <th> Start Time</th>
                <th>End Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($shifts as $shift) : ?>
                <tr>
                    <td><?php echo $shift['shift_name']; ?></td>
                    <td><?php $start_time= $shift['start_time']; 
                    echo date('H:i', strtotime($start_time) - 60);
                    ?></td>
                    <td><?php $end_time= $shift['end_time']; 
                    echo date('H:i', strtotime($end_time) + 60);
                    ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="shift_id" value="<?php echo $shift['shift_id']; ?>">
                            <input type="text" name="new_shift_name" placeholder="Enter new shift name">
                            <button type="submit" name="edit_shift" onclick="return confirm('Are you sure you want to edit this shift?')">Rename</button>
                            <button type="submit" name="delete_shift" onclick="return confirm('Are you sure you want to delete this shift?')">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>



    <div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
    <button class="home-button" onclick="location.href='shift.php'">Back</button>
</body>

</html>