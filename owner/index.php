<?php
session_start();

include '../includes/db_connect.php';
// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

function get_dashboard_summary($library_id) {
    global $conn;

    // Total number of students
    $sql_students = "SELECT COUNT(*) AS total_students FROM students WHERE library_id = ?";
    $stmt_students = $conn->prepare($sql_students);
    $stmt_students->bind_param("i", $library_id);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();
    $row_students = $result_students->fetch_assoc();
    $total_students = $row_students['total_students'];

    // Total number of seats
    $sql_seats = "SELECT COUNT(*) AS total_seats FROM seats WHERE library_id = ?";
    $stmt_seats = $conn->prepare($sql_seats);
    $stmt_seats->bind_param("i", $library_id);
    $stmt_seats->execute();
    $result_seats = $stmt_seats->get_result();
    $row_seats = $result_seats->fetch_assoc();
    $total_seats = $row_seats['total_seats'];

    // Total number of occupied seats
   // $sql_occupied_seats = "SELECT COUNT(*) AS occupied_seats FROM seats WHERE library_id = ? AND is_occupied = 1";
    //$stmt_occupied_seats = $conn->prepare($sql_occupied_seats);
    //$stmt_occupied_seats->bind_param("i", $library_id);
    //$stmt_occupied_seats->execute();
    //$result_occupied_seats = $stmt_occupied_seats->get_result();
    //$/row_occupied_seats = $result_occupied_seats->fetch_assoc();
    //$occupied_seats = $row_occupied_seats['occupied_seats'];
    //$available_seats = $total_seats - $occupied_seats;
    $available_seats=" ";
    $occupied_seats =" ";

// Total number of shifts
$sql_shifts = "SELECT COUNT(*) AS total_shifts FROM shifts WHERE library_id = ?";
$stmt_shifts = $conn->prepare($sql_shifts);
$stmt_shifts->bind_param("i", $library_id);
$stmt_shifts->execute();
$result_shifts = $stmt_shifts->get_result();
$row_shifts = $result_shifts->fetch_assoc();
$total_shifts = $row_shifts['total_shifts'];

    return array(
        'total_students' => $total_students,
        'total_seats' => $total_seats,
        'available_seats' => $available_seats,
        'occupied_seats' => $occupied_seats,
        'total_shifts' => $total_shifts
    );
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

function get_library_id_by_user_id_and_role($user_id, $role) {
    global $conn;

    $sql = "SELECT library_id FROM owners WHERE owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['library_id'] ?? null;
}

$library_id = get_library_id_by_user_id_and_role($user_id, $role);


// Get dashboard summary
$dashboard_summary = get_dashboard_summary($library_id);

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f2f2f2;
            margin: 20px;
            background: linear-gradient(to right, #f27121, #e94057);
        }
        table {
        border-collapse: collapse;
        width: 100%;
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

        h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 30px;
        }

        a {
            text-decoration: none;
            background-color: #4CAF50;
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            margin: 5px;
            display: inline-block;
        }

        h2 {
            font-size: 24px;
            color: #333;
        }

        p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        
        header {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px;
            border-radius: 5px;
            background: #b000b0; 
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
            background-color: transparent;
        }

        header h1 {
  font-family: 'Montserrat', sans-serif; /* Replace with your preferred font */
  font-size: 3rem;
  color: #333;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  animation: fadeInUp 1s ease-in-out;
}
.aside-1{
    border: 1px solid #ddd;
            padding: 20px;
            margin: 20px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            width:50%;
        }
        .aside-2 {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px;
            border-radius: 5px;
            background-color: #fff;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            width:50%;
            
        }
        .dashboard-summary {
            border: 1px solid #ddd;
            padding: 20px;
            margin: 20px;
            border-radius: 5px;
            background: linear-gradient(to top, #f27341, #e94076);
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            display:flex;

        }


@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0); }
  }
  li{
    list-style: none;
    
  }
    </style>
</head>
<header><h1>Owner Dashboard</h1></header>
<body>
    
    
    <a href="seat_management.php">Add Seat</a>
    <a href="shift.php">Add shift</a>
    <a href="add_student.php">Add student</a>
    <a href="student_management.php">Edit Student</a>
    <a href="library_info.php">About Library</a>
    <a href="student_list.php">student_list</a>
    <a href="seating_arrangement.php">seating </a>
    <a href="notice.php">Notice</a>
    <a href="complaints.php">complaints</a>
    

<div class="dashboard-summary">

    <div class= "aside-1">
        <h2>Dashboard Summary</h2>
        <p>Total Students: <?php echo $dashboard_summary['total_students']; ?></p>
        <p>Total Seats: <?php echo $dashboard_summary['total_seats']; ?></p>
        <p>Total Shifts: <?php echo $dashboard_summary['total_shifts']; ?></p>

    </div>
    <div class= "aside-2">
    <?php
include '../includes/db_connect.php';

// Check connection
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$library_id = get_library_id_by_user_id_and_role($user_id, $role);
// Query to get students with pending payment and due dates within the next 5 days
$sql = "SELECT s.name, sf.fee_id, sf.due_date, sf.purpose
       FROM students s
       JOIN student_fees sf ON s.student_id = sf.student_id
       WHERE s.library_id = ?
       AND (sf.payment_status = 'pending'
           OR sf.due_date < CURDATE())
       AND sf.due_date <= DATE_ADD(CURDATE(), INTERVAL 5 DAY)
       ORDER BY sf.due_date ASC;
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $library_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
  die("Query failed: " . $conn->error);
}

if ($result->num_rows > 0) {
  echo "<h2>Students with Pending Dues upto upcomming 5 Days</h2>";
  
  echo "<table border='1'>";
  echo "<tr><th>Name</th><th>Fee ID</th><th>Due Date</th><th>Purpose</th></tr>";

  while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row["name"] . "</td>";
    echo "<td>" . $row["fee_id"] . "</td>";
    echo "<td>" . $row["due_date"] . "</td>";
    echo "<td>" . $row["purpose"] . "</td>";
    echo "</tr>";
   
  }

  echo "</table>";
} else {
  echo "<p>No students with pending payments due within the next 5 days.</p>";
  
}

$conn->close(); // Close the connection

?>
</div>
</div>

</body>

<a href="../logout.php">logout</a>
</html>

