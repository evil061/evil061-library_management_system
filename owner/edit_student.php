<?php
session_start();

include '../includes/db_connect.php';
// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}


$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$library_id = get_user_library($user_id);
$student_id = $_GET['student_id'];


// Fetch student details
function get_students_details($student_id) {
    global $conn;
    $sql = "SELECT * FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    return $student;
}

$student = get_students_details($student_id);

function get_user_library($user_id) {
    global $conn;
    $sql = "SELECT library_id FROM owners WHERE owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
  
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['library_id'];
    } else {
        return false; // Or handle user not found
    }
  }

$user_id = $_SESSION['user_id'];

function display_shift_name($shift_id) {
    global $conn; // Assuming you have a global $conn variable for the database connection
  
    // Prepare the SQL statement to fetch the shift name
    $sql = "SELECT shift_name FROM shifts WHERE shift_id = ?";
    $stmt = $conn->prepare($sql);
  
    // Bind the shift ID parameter to the statement
    if (!$stmt->bind_param("i", $shift_id)) {
        // Handle binding parameter error
        echo "Error binding parameter: " . $stmt->error;
        exit;
    }
  
    // Execute the statement
    if (!$stmt->execute()) {
        // Handle execution error
        echo "Error executing statement: " . $stmt->error;
        exit;
    }
  
    // Fetch the result
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
  
    // Check if a shift was found
    if ($row) {
        // Shift found, return the name
        return $row['shift_name'];
    } else {
        // Shift not found, return an appropriate message or null
        return "Shift not found"; // Or you can return null
    }
  }

  function fetch_shift_ids_by_library_id($library_id) {
    global $conn;
  
    // Prepare the SQL statement
    $sql = "SELECT shift_id FROM shifts WHERE library_id = ?";
    $stmt = $conn->prepare($sql);
  
    // Bind the parameter
    $stmt->bind_param("i", $library_id);
  
    // Execute the statement
    $stmt->execute();
  
    // Get the result set
    $result = $stmt->get_result();
  
    // Fetch the shift IDs
    $shift_ids = array();
    while ($row = $result->fetch_assoc()) {
        $shift_ids[] = $row['shift_id'];
    }
  
    return $shift_ids;
  }

  function due_date($student_id) {
    global $conn;
    $sql = "SELECT due_date FROM student_fees WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
  
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['due_date'];
    } else {
        return false; // Or handle user not found
    }
  }
  
$library_id = get_user_library($user_id);
$available_shifts = fetch_shift_ids_by_library_id($library_id);

?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>
<style>
    
body {
  font-family: Arial, sans-serif;
  margin: 20px;
  background: linear-gradient(to right, #f27121, #e94057);
}

.container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 5px;
  background-color: #f0f0f0;
  width:400px;
}

h2 {
  text-align: center;
  margin-bottom: 20px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

label {
  font-weight: bold;
}

input[type="text"],
input[type="email"],
input[type="number"],
input[type="password"],
input[type="date"],
select {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

button[type="submit"] {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button[type="submit"]:hover {
  background-color: #45a049;
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


.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #f0f0f0;
}

@media (max-width: 768px) {
    .container {
        max-width: 100%; /* Make the container full width on smaller screens */
    }
}


/* Rest of your CSS */

</style>
<body>
    <div class="container">
    <h2>Edit Student</h2>
    <form method="post" action= "update_student.php">
        <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?php echo $student['name']; ?>" required>
        <label for="name">Max shifts:</label>
        <input type="number" id="max_bookings" name="max_bookings" value="<?php echo $student['max_bookings']; ?>" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo $student['email']; ?>" required>    
        <label for="alotted_shift">Shift:</label>
        <select id="seat_number" name="allotted_shift" required>
            <?php foreach ($available_shifts as $shift) : ?>      
                <option value="<?php echo $shift?>"><?php echo display_shift_name($shift); ?></option>
            <?php endforeach; ?>
        </select>
        <label for="email">password:</label>
        <input type="password" id="password" name="password" value="<?php //echo $student['password']; ?>" required>
        
        <button type="submit" >Update</button>
    </form>
            </div>
</body>
</html>