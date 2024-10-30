<?php
session_start();

include '../includes/db_connect.php';

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

// Functions
function get_user_library($user_id) {
    global $conn;
    $sql = "SELECT library_id FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['library_id'] : false;
}

function get_library_name($library_id) {
    global $conn;
    $sql = "SELECT library_name FROM libraries WHERE library_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc()['library_name'] : "Library not found";
}

function getUserDetails($user_id) {
    global $conn;
    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getCurrentSeat($user_id) {
    global $conn;

    $sql = "SELECT seats.seat_number
            FROM seat_shifts
            JOIN seats ON seat_shifts.seat_id = seats.seat_id
            WHERE seat_shifts.student_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc()['seat_number'];
    } else {
        return false;
    }
}

function getCurrentShift($user_id) {
    global $conn;
    $sql = "SELECT allotted_shift FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0 ? $result->fetch_assoc() : false;
}

function get_shift_name_by_id($shift_id) {
    global $conn;
    $sql = "SELECT shift_name FROM shifts WHERE shift_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $shift_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['shift_name'];
    } else {
        return false; // Or handle the case where no shift is found
    }
}

function display_all_library_notices($library_id) {
    global $conn;

    $sql = "SELECT * FROM notifications WHERE library_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<li>' . $row['message'] . '</li>';   
        }
    } else {
        echo '<li>No notices found for this library.</li>';
    }
    $student_id= $_SESSION['user_id'];
    $current_student_id = $student_id; // Replace with the actual current student ID

    // Query to get students with due dates within the next 5 days for the current student
    $sql = "SELECT s.name, sf.fee_id, sf.due_date , sf.purpose
    FROM students s 
    JOIN student_fees sf ON s.student_id = sf.student_id 
    WHERE sf.student_id = $current_student_id AND sf.due_date <= DATE_ADD(CURDATE(), INTERVAL 5 DAY) AND sf.payment_status != 'paid'
    ";
    
    $result = $conn->query($sql);
    
    if (!$result) {
    die("Query failed: " . $conn->error);
    }
    
    if ($result->num_rows > 0) {
    echo "<h2>Pending Dues </h2>";
    echo "<ul>";
    
    while($row = $result->fetch_assoc()) {
    echo "<li>";
    echo "Name: " . $row["name"] . "<br>";
    echo "Fee ID: " . $row["fee_id"] . "<br>";
    echo "Due Date: " . $row["due_date"] . "<br>";
    echo "Purpose: " . $row["purpose"] . "<br><br>";
    echo "</li>";
    }
    
    echo "</ul>";
    }

}

$user_id = $_SESSION['user_id'];
$library_id = get_user_library($user_id);

    function get_notifications($library_id) {
        global $conn;
        $sql = "SELECT * FROM notifications WHERE library_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $library_id);
        $stmt->execute();
        $result = $stmt->get_result();
    
        // Get the number of rows
        $num_rows = $result->num_rows;
    
        if ($result->num_rows > 0) {
            return $num_rows;
        } else {
            return 0;
        }
    
        $stmt->close();
        $result->close();
    }


// Main script
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    if ($user_role === 'student') {
        $library_id = get_user_library($user_id);
        $userDetails = getUserDetails($user_id);
        $currentSeat = getCurrentSeat($user_id);
        $currentShift = getCurrentShift($user_id);
        $libraryName = get_library_name($library_id);
    } else {
        // Other roles
        $conn->close();
    }
} else {
    header('Location: ../login.php');
    exit;
}
 $notifications = get_notifications($library_id);

?>

        <!DOCTYPE html>
        <html>
        <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Student Details</title>
            <link rel="stylesheet" href="styles.css">
            <script src="../js/jquery-3.6.0.min.js"></script>
        </head>
        <style>

        </style>
        <body>
            <div class="container">
                <h1> Student Details</h1>
                <div class="details">
                    <p>Name: <input type="text" value="<?php echo $userDetails['name']; ?>" disabled></p>
                    <p>Student ID: <input type="text" value="<?php echo $user_id; ?>" disabled></p>
                    <p>Library: <input type="text" value="<?php echo $libraryName; ?>" disabled></p>
                    <p>Current Seat: <input type="text" value="<?php echo $currentSeat ? $currentSeat : "No seat assigned"; ?>" disabled></p>
                    <?php if ($currentShift): ?>
                        <p>Current Shift:  <input type="text" value="<?php echo get_shift_name_by_id($currentShift['allotted_shift']); ?>" disabled></p>
                    <?php else: ?>
                        <p>No shift assigned</p>
                    <?php endif; ?>
                </div>
                <div class="buttons">
                    <button onclick="window.location.href='logout.php'">Logout</button>
                    <button onclick="window.location.href='fees.php'">fees</button>
                    <button onclick="window.location.href='seat_management.php'">Manage</button>
                    <button onclick="window.location.href='complains.php'">Complains</button>
                </div>
            </div>
                    

<div class="notification">
            
    <div id="registrationForm" style="display: none;">
        <form onsubmit="sendContact(event)">
      
        <div class="container">
            <h3> NOTIFICATION</h3>
            <li><?php echo display_all_library_notices($library_id);  ?></li>
            
        </div>
    </form>
    </div>

  <p class="notification-icon" id="show_notification" onclick="toggleForm()"><img src= " bell.png" height="20px" width= "20px" alt= " icon"><span class="notification-count"><?php echo $notifications ?></span></p>
  

  <script>
    function toggleForm() {
      $("#registrationForm").toggle();
    }
  </script>
            
</div>
</body>
</html>

