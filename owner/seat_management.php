<?php
session_start();

include '../includes/db_connect.php';
include '../includes/functions.php';

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}


function get_seats($library_id) {
    global $conn;
    $sql = "SELECT * FROM seats WHERE library_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $seats = array();
    while ($row = $result->fetch_assoc()) {
        $seats[] = $row;
    }
    return $seats;
}

function add_seat($seat_number, $library_id) {
    global $conn;
    $is_occupied = 0;
    $sql = "INSERT INTO seats (seat_number, library_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $seat_number, $library_id);
    return $stmt->execute();
}


function remove_seat($seat_id) {
    global $conn;
    $sql = "DELETE FROM seats WHERE seat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seat_id);
    return $stmt->execute();
    // delete from seat shifts 
    global $conn;
    $sql = "DELETE FROM seat_shifts WHERE seat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $seat_id);
    return $stmt->execute();
}

function modify_seat($seat_id, $new_seat_number) {
    global $conn;
    $sql = "UPDATE seats SET seat_number = ? WHERE seat_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_seat_number, $seat_id);
    return $stmt->execute();
}

function fetch_shifts_by_library_id($library_id) {
    global $conn;
    $sql = "SELECT * FROM shifts WHERE library_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $shifts = array();
    while ($row = $result->fetch_assoc()) {
        $shifts[] = $row;
    }
    return $shifts;
}
//echo json_encode(fetch_shifts_by_library_id(3));


function create_seats_for_all_shifts($library_id, $seat_number) {
    global $conn;

    // Get all shifts for the given library
    $shifts = fetch_shifts_by_library_id($library_id);

    // Insert the new seat into the seats table (auto-increment will handle the ID)
    $sql = "INSERT INTO seats (seat_number, library_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $seat_number, $library_id);
    $stmt->execute();

    // Get the ID of the newly inserted seat
    $new_seat_id = $stmt->insert_id;

    // Create seats for each shift
    foreach ($shifts as $shift) {
        $shift_id = $shift['shift_id'];

        // Insert the seat-shift association into the seat_shifts table
        $sql = "INSERT INTO seat_shifts (seat_id, shift_id) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_seat_id, $shift_id);
        $stmt->execute();
    }

    return true;
}


// Get the library ID from the session
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$library_id = get_library_id_by_user_id_and_role($user_id, $role);

// Get the list of seats
$seats = get_seats($library_id);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_seat'])) {
        $seat_number = $_POST['seat_number'];
        if (add_seat($seat_number, $library_id)) {
            // Successful addition, redirect or display success message
            header('Location: seat_management.php'); // Replace with desired redirect URL
            exit;
        } else {
            // Handle error
        }
    } elseif (isset($_POST['remove_seat'])) {
        $seat_id = $_POST['seat_id'];
        if (remove_seat($seat_id)) {
            // Successful removal, redirect or display success message
            header('Location: seat_management.php'); // Replace with desired redirect URL
            exit;
        } else {
            // Handle error
        }
    } elseif (isset($_POST['modify_seat'])) {
        $seat_id = $_POST['seat_id'];
        $new_seat_number = $_POST['new_seat_number'];
        if (modify_seat($seat_id, $new_seat_number)) {
            // Successful modification, redirect or display success message
            header('Location: seat_management.php'); // Replace with desired redirect URL
            exit;
        } else {
            // Handle error
        }
    } elseif (isset($_POST['num_seats'])) {
        $num_seats = $_POST['num_seats'];
        for ($i = 1; $i <= $num_seats; $i++) {
            $seat_number = "Seat " . $i;
            create_seats_for_all_shifts($library_id, $seat_number);
        }
        // Successful addition, redirect or display success message
        header('Location: seat_management.php'); // Replace with desired redirect URL
        exit;
    } elseif (isset($_POST['add_multiple_seats'])) {
        $seat_numbers = explode(',', $_POST['seat_numbers']);
        $seat_numbers = array_map('trim', $seat_numbers); // Remove extra spaces
    
        if (count($seat_numbers) > 50) {
            // Handle error: Too many seats
            echo "<script>alert('You cannot create more than 50 seats at once.'); window.location.href = 'seat_management.php';</script>";
            exit;
        } else {
            foreach ($seat_numbers as $seat_number) {
                create_seats_for_all_shifts($library_id, $seat_number);
            }
            // Successful addition, redirect or display success message
            //header('Location: seat_management.php'); // Replace with desired redirect URL
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Seat Management</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
</head>
<style>

    table {
        border-collapse: collapse;
        width: 50%;
        margin-top:40px;
        margin-right:20px;
    }

    th, td {
        padding: 10px;
        border: 1px solid #ccc;
        text-align: left;
    }

    th {
        background-color: #f0f0f0;
        font-weight: bold;
    }

    h2 {
        text-align: center;
        margin-bottom: 20px;
        position:fixed;
        top:0;
        left: 40%;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    label {
        font-weight: bold;
    }

    input[type="text"],
    input[type="number"] {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    button[type="submit"] {
        background-color: #4CAF50;
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button[type="submit"]:hover {
        background-color: #45a049;
    }

    body {
        display: flex;
        justify-content: space-between;
        align-items: flex-start; /* Align items to the top */
        margin-top: 20px;
        padding: 20px;
        background: linear-gradient(to right, #f27121, #e94057);
    }

    .seat-management-container {
        flex: 2;
    }

    .add-seat-container {
        width: 100px;
        background-color: #f0f0f0;
        border-radius: 10px;
        box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
        padding: 20px;
    }

    .home-button-container {
        align:center;
        position:fixed;
        right:10%;
        top:55%;
    }

    .home-button {
    }
    .section{
        margin-top:30px;
        gap:20px 50px;
        margin-right:10%;
        margin-top:40px;
        background-color: rgba(255, 255, 255, 0.8);
        border-radius: 10px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        padding: 20px;
        width: 200px;

    }
</style>

<body>
<h2>Seat Management</h2>
    <table>

        <thead>
            <tr>
                <th>Seat Number</th>
                <th>Change Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($seats as $seat) : ?>
            <tr>
                <td><?php echo $seat['seat_number']; ?></td>

                <td>
                    <form method="post">

                        <input type="text" name="new_seat_number" value="<?php echo $seat['seat_number']; ?>">
                        <button type="submit" name="modify_seat">Modify</button>
                    </form>
                </td>
                
                <td>
                    <form method="post">
                        <input type="hidden" name="seat_id" value="<?php echo $seat['seat_id']; ?>">
                        <button type="submit" name="remove_seat">Remove</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<div class= "section">
    <form method="post">
        <label for="num_seats">Number of Seats:</label>
        <input type="number" id="num_seats" name="num_seats" min="1" required>
        <button type="submit" name="add_seats_by_number">Add Seats</button>
    </form>
    <form method="post">
        <label for="seat_numbers">Seat Name (comma-separated):</label>
        <input type="text" id="seat_numbers" name="seat_numbers" required>

        <button type="submit" name="add_multiple_seats">Add Seats</button>
    </form>
            </div>
    <div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
</body>
</html>