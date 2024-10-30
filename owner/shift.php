<?php
session_start();
include("../includes/db_connect.php");
$user_id = $_SESSION['user_id'];
function get_library_id($user_id) {
  global $conn;
  $sql = "SELECT library_id FROM owners WHERE owner_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $row = $result->fetch_assoc();
  return $row['library_id'];
}

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    if ($user_role === 'owner') {
        // User is an owner

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Get form data
            $day = $_POST['day'];
            $shift_name = $_POST['shift_name'];
            $s_time = $_POST['start_time'];
            $e_time = $_POST['end_time'];
            $start_time = date('H:i', strtotime($s_time) + 60);
            $end_time = date('H:i', strtotime($e_time) - 60);
            // Get library_id based on user_id
            $library_id = get_library_id($user_id);

            // Check for duplicate shift name within the same library
            $check_sql = "SELECT COUNT(*) AS count FROM shifts WHERE shift_name = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $shift_name);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            $row = $check_result->fetch_assoc();


                // Prepare and execute SQL statement for creating the shift
                $sql = "INSERT INTO shifts (shift_name, start_time, end_time, library_id) VALUES (?, ?, ?,?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $shift_name, $start_time, $end_time,$library_id);

                if ($stmt->execute()) {
                    // Get the ID of the newly inserted shift
                    $shift_id = $stmt->insert_id;

                    // Get available seats for the library
                    $seat_sql = "SELECT seat_id FROM seats WHERE library_id = ?";
                    $seat_stmt = $conn->prepare($seat_sql);
                    $seat_stmt->bind_param("i", $library_id);
                    $seat_stmt->execute();
                    $seat_result = $seat_stmt->get_result();

                    // Insert shift-seat assignments
                    while ($seat_row = $seat_result->fetch_assoc()) {
                        $seat_id = $seat_row['seat_id'];
                        $seat_shift_sql = "INSERT INTO seat_shifts (shift_id, seat_id) VALUES (?, ?)";
                        $seat_shift_stmt = $conn->prepare($seat_shift_sql);
                        $seat_shift_stmt->bind_param("ii", $shift_id, $seat_id);
                        $seat_shift_stmt->execute();
                    }
                    // ... (rest of your success message and redirection)
                    $_SESSION['success'] = "Shift added successfully!";
                    header('Location: shift.php');
                    exit;
                } else {
                    $_SESSION['error'] = "Error: " . $stmt->error;
                }
            

            $stmt->close();
        }
    } else {
        // User is not an owner, redirect
        header('Location: ../login.php');
        exit;
    }
} else {
    // User is not logged in, redirect
    header('Location: ../login.php');
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Shift</title>
    <link rel="stylesheet" href="styles.css"> </head>
    <style>

body {
  
  font-family: Arial, sans-serif;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(to right, #f27121, #e94057);
}

.notification {
  /* Styles for the notification container */
  
}

.error {
  /* Styles for error messages */
  color: red;
  animation: fadeOut 2s;
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}

.success {
  /* Styles for success messages */
  color: green;
  animation: fadeOut 2s;
}

@keyframes fadeOut {
  from {
    opacity: 1;
  }
  to {
    opacity: 0;
  }
}


#container {
  margin-top:40px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 10px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 400px;
}

h2 {
  text-align: center;
  margin-bottom: 30px;
}

form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

label {
  font-weight: bold;
}

input {
  padding: 12px 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  outline: none;
  transition: border-color 0.3s ease-in-out;
}

input:focus {
  border-color: #4CAF50;
}

#button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
}

button:hover {
  background-color: #45a049;
}

.error-message {
  color: red;
  text-align: center;
  margin-bottom: 15px;
}
@media only screen and (max-width: 768px) { /* Adjust breakpoint as needed */
  #container {
    margin-top: 20px; /* Adjust margin as needed */
  }
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
header {
  
  padding: 0px;
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
}


header h1 {
  font-family: 'Montserrat', sans-serif; /* Replace with your preferred font */
  font-size: 3rem;
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

        </style>
        <body>
    
    <div id= "container">
<header>
    <h1>Add Shift</h1>
</header>
    <div class ="notification">
    <?php if (isset($_SESSION['error'])) : ?>
        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['success'])) : ?>
        <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?> </div>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

        <label for="shift_name">Shift Name:</label>
        <input type="text" name="shift_name" required><br>
        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" required><br>
        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" required><br>
        <div class= button>
    <input type="submit" value="Add Shift">
    <button class="home-button" onclick="location.href='manage_shift.php'"> manage shift </button>
    </div>
    </form>
    </div>

    

</body>
<div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
</html>
