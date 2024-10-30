<?php
function hash_password($password) {
    $salt = dechex(mt_rand(0, 2147483647)) . dechex(mt_rand(0, 2147483647));
    $password = $password . $salt;
    return crypt($password, '$2y$11$' . $salt);
}

function verify_password($password, $hashed_password) {
    return password_verify($password, $hashed_password);
}

function validate_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
function getUserDetails($user_id) {
  global $conn;
  $sql = "SELECT * FROM users WHERE user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result(); 
  return $result->fetch_assoc();
}
function assign_library_to_student($student_id, $library_id) {
  global $conn;

  $sql = "UPDATE students SET library_id = ? WHERE student_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $library_id, $student_id);
  $result = $stmt->execute();
  $stmt->close();

  return $result;
}

function get_libraries() {
  global $conn;

  $sql = "SELECT library_id, name FROM libraries";
  $result = $conn->query($sql);
  $libraries = array();
  while ($row = $result->fetch_assoc()) {
      $libraries[] = $row;
  }
  return $libraries;
}
function assign_library_to_user($user_id, $library_id) {
  global $conn;

  $sql = "UPDATE users SET library_id = ? WHERE user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $library_id, $user_id);
  $result = $stmt->execute();
  $stmt->close();

  return $result;
}
function get_logged_in_user_id() {
  // Check if the user is logged in
  if (isset($_SESSION['user_id'])) {
    return $_SESSION['user_id'];
  } else {
    // Handle user not logged in (e.g., redirect to login page)
    echo "user not logged in";
    return false; // or throw an exception
  }
}
function getUserData($user_id) {
  global $conn;

  $sql = "SELECT name FROM students WHERE user_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      return $row;
  } else {
      return false; // Or handle user not found
  }
}


function get_available_seats($library_id) {
  global $conn;
  $sql = "SELECT seat_number FROM seats WHERE library_id = ? AND is_occupied = 0";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $library_id);
  $stmt->execute();
  $result = $stmt->get_result();
  $seats = array();
  while ($row = $result->fetch_assoc()) {
      $seats[] = $row['seat_number'];
  }
  return $seats;
}

function book_seat($seat_number, $library_id, $student_id) {
  global $conn;
  $sql = "UPDATE seats SET is_occupied = 1, student_id = ? WHERE seat_number = ? AND library_id = ? AND is_occupied = 0";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iii", $student_id, $seat_number, $library_id);
  $result = $stmt->execute();

  // Check for errors
  if (!$result) {
      error_log("Error booking seat: " . $stmt->error);
      return false;
  }

  return true;
}

function release_seat($seat_number, $library_id) {
  global $conn;
  $sql = "UPDATE seats SET is_occupied = 0, student_id = NULL WHERE seat_number = ? AND library_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("ii", $seat_number, $library_id);
  return $stmt->execute();
}

function get_user_library($user_id) {
  global $conn;
  $sql = "SELECT library_id FROM students WHERE user_id = ?";
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

function get_library_name($library_id) {
  global $conn;
  $sql = "SELECT library_id FROM libraries WHERE library_id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $library_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      return $row['library_id'];
  } else {
      return "Library not found"; // Or handle error differently
  }
}
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

?>
