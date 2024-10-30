<?php
session_start();

include '../includes/db_connect.php';
include '../includes/functions.php';

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
  // Redirect to the login page or an appropriate access denied page
  header('Location: ../login.php');
  exit();
}


$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$library_id = get_library_id_by_user_id_and_role($user_id, $role);
$available_shifts = fetch_shift_ids_by_library_id($library_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (!isset($_POST['allotted_shift']) || empty($_POST['allotted_shift'])) {
      // Handle the case where allotted_shift is not set or empty
      echo json_encode(array('success' => false, 'message' => 'Please select a shift'));
      exit;
  }

  $allotted_shift = (int)$_POST['allotted_shift'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Find an unoccupied seat
    $seat_number = get_unoccupied_seat($library_id);
    if (!$seat_number) {
        // Handle no available seats
        echo '<script>
        alert("Seats/shift not available");
        window.location.href = "seat_management.php";
        </script>';
        exit;
    }
    $sql = "SELECT COUNT(*) AS count FROM students WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $email_count = $row['count'];

    if ($email_count > 0) {
      // Email exists, return JSON response
      echo json_encode(array('success' => false, 'message' => 'Email already exists'));
      exit;
  } else {
      // Email is unique, proceed with insertion
      // ... rest of your code for inserting student and seat
      //echo json_encode(array('success' => true));
  }

    // Begin transaction
    $conn->begin_transaction();

    // Insert student into the database
    $sql = "INSERT INTO students (name, email, password, library_id, allotted_shift) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $name, $email, $hashed_password, $library_id, $allotted_shift);
    if (!$stmt->execute()) {
        $conn->rollback();
        echo "Error adding student"; // Replace with proper error handling
        exit;
    }
    $student_id = $stmt->insert_id;   
       
// Get the joining date from the students table
    $sql = "SELECT joining_date FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $joining_date = $row['joining_date'];

// Insert into student_fees table
    $amount_paid=0;
    $payment_status="pending";
    $due_date=$joining_date;
    $selectedMonth = date('F', strtotime($joining_date));
    $purpose= "Library_fees";

   
$sql = "INSERT INTO student_fees (student_id, amount_paid, payment_status, due_date, month, purpose) VALUES ('$student_id', '$amount_paid', '$payment_status', '$joining_date', '$selectedMonth', '$purpose')";

if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
    header('Location: ../fees/registration_fees.php?student_id=' . $student_id); // Redirect to student list
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}



    $conn->commit();
    
    
    exit;
}

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

function get_unoccupied_seat($library_id) {
    global $conn;
    $sql = "SELECT seat_number FROM seats WHERE library_id = ? ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $library_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['seat_number'];
    } else {
        return false;
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

function show_library_shifts($library_id) {
  global $conn;
  $sql = "SELECT * FROM shifts WHERE library_id = ?"; // Use library_id instead of seat_id
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $library_id);
  $stmt->execute();
  $shifts_result = $stmt->get_result();
  $shifts = $shifts_result->fetch_all(MYSQLI_ASSOC);
  // Check if any shifts were found
  if (empty($shifts)) {
      // Handle case where no shifts were found
      echo "No shifts found for library ID: " . $library_id;
  } else {
      // Process and display the shifts
      foreach ($shifts as $shift) {
          // Access shift data using $shift['column_name']
          echo "Shift ID: " . $shift['shift_id'] . "<br>";
          echo "Start Time: " . $shift['start_time'] . "<br>";
          echo "End Time: " . $shift['end_time'] . "<br>";
          // ... other shift properties
      }
  }
}

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student</title>
</head>
<style>

body {
  font-family: Arial, sans-serif;
  margin: 0;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(to right, #f27121, #e94057);
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
input[type="password"],
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
    margin-top:30px;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 15px;
    background-color: #f0f0f0;
    width:400px;
}

@media (max-width: 768px) {
    .container {
        max-width: 100%; /* Make the container full width on smaller screens */
    }
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
  font-size: 2rem;
  color: #333;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  animation: fadeInUp 1s ease-in-out;
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

/* Rest of your CSS */
<?php  ?>
</style>
<body>
    <div class="container">
    <header><h1>Add Student</h1></header>
    <form method="post" action="add_student.php">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <label for="seat_number">Shift Name:</label>
        <select id="seat_number" name="allotted_shift" required>
            <?php foreach ($available_shifts as $shift) : ?>      
                <option value="<?php echo $shift?>"><?php echo display_shift_name($shift); ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Add Student</button>
    </form>
            </div>
</body>
<div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
</html>
