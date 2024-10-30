<?php
session_start();

include 'includes/db_connect.php';
include 'includes/functions.php';

function login($username, $password) {
    global $conn;

    // Check in students table
    $sql_students = "SELECT student_id as user_id, 'student' as role, password FROM students WHERE email = ?";
    $stmt_students = $conn->prepare($sql_students);
    $stmt_students->bind_param("s", $username);
    $stmt_students->execute();
    $result_students = $stmt_students->get_result();

    // Check in owners table
    $sql_owners = "SELECT owner_id as user_id, 'owner' as role, password FROM owners WHERE email = ?";
    $stmt_owners = $conn->prepare($sql_owners);
    $stmt_owners->bind_param("s", $username);
    $stmt_owners->execute();
    $result_owners = $stmt_owners->get_result();
    // Check in users table
    $sql_admin = "SELECT admin_id as user_id, 'admin' as role, password FROM users WHERE email = ?";
    $stmt_admin = $conn->prepare($sql_admin);
    $stmt_admin->bind_param("s", $username);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    // Check if user exists in either table
    if ($result_students->num_rows === 1) {
        $row = $result_students->fetch_assoc();
    } elseif ($result_owners->num_rows === 1) {
        $row = $result_owners->fetch_assoc();
    } elseif ($result_admin->num_rows === 1) {
        $row = $result_admin->fetch_assoc();
    } else {
        return "User not found";
    }

    if (password_verify($password, $row['password'])) { // Using built-in password_verify
        // Successful login
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['role'] = $row['role'];
        return true;
    } else {
        return "Incorrect password";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = validate_input($_POST['username']);
    $password = validate_input($_POST['password']);

    $result = login($username, $password);

    if ($result === true) {
        // Redirect based on role
        if ($_SESSION['role'] === 'student') {
            header('Location: student/index.php');
        } elseif ($_SESSION['role'] === 'owner') {
            header('Location: owner/index.php');
        } elseif ($_SESSION['role'] === 'admin') {
            header('Location: admin/index.php');
      }
        exit;
    } else {
        $error_message = $result;
    }
}
?>
<!DOCTYPE html>
<html>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<head>
  <title>Login Page</title>
  <link rel="stylesheet" href="">
</head>
<style>
    body {
  font-family: Arial, sans-serif;
  display:flex;
  justify-content: center;
  align-items: center;
  height: 60vh;
  margin: 0;
}
.container {
  background-color: #f0f0f0;
  border-radius: 10px;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 300px;
  text-align: center;
  margin-top: 30px;
  margin-left:20px; 
  height:310px;
  margin-right:20px;
 
}

h1 {
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
p{
  text-align:center;
}
input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
button:hover {
  background-color: #45a049;
}

.error {
  color: red;
  text-align: center;
  margin-bottom: 10px;
}
.register-button-container{
  align:center;
  position:fixed;
  left:35%;
  top:65%;
}
footer {
  background-color: #f5f5f5;
  padding: 20px;
  text-align: center;
  color: #666;
  border-top: 1px solid #ccc;
  width: 100%;
  position:fixed;
  left:0;
  bottom:0; 
}
.imgcontainer {
  background-color: #f0f0f0;
  border-radius: 10px;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
  width: 370px;
  text-align: center;
  margin-top: 30px;
  margin-right:30px; 
  height:390px;
  display: none;
}
.imgcontainer img{
height:100%;
width:100%;
border-radius:15px;
box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
}
@media only screen and (min-width: 481px) { /* Adjust the breakpoint as needed */
    .imgcontainer {
        display: block; /* Show the image on screens wider than 480px */
    }
}
</style>
<body>
  <div class="imgcontainer">
    <img src="images.jpeg">
</div>
  <div class="container">
    <h1>Login</h1>
    <?php if (isset($error_message)) : ?>
      <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>
    <form method="post" action="login.php">
      <label for="username">Email:</label>
      <input type="text" id="username" name="username" required>
      <label for="password">Password:</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Login</button>
    </form>
  </div>
  <br>
  <br />
  <div class="register-button-container">
    <p>Register new library : <button class="register-button" onclick="location.href='owner_register.php'">Register</button></p>
  </div>
</body>
</html>
<footer>
  <p>&copy; 2024 Library Management System | Developed by [Tushar] | Contact us: <a href="mailto:tusharrastogi061@gmail.com">contact@gmail.com</a></p>
  <p><a href="contact/term.php">Terms of Use</a> | <a href="contact/privacy_policy.php">Privacy Policy</a> | <a href="contact/about.php">About us</a></p>
</footer>