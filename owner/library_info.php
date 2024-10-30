<?php
session_start();

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
  // Redirect to the login page or an appropriate access denied page
  header('Location: ../login.php');
  exit();
}


include '../includes/db_connect.php';
include '../includes/functions.php';
$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$library_id = get_library_id_by_user_id_and_role($user_id, $role);

 // Assuming library_id is stored in session

// Fetch library details
$sql = "SELECT * FROM libraries WHERE library_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $library_id);
$stmt->execute();
$result = $stmt->get_result();
$library = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle form submission
    $library_id = $_POST['library_id'];
    $library_name = $_POST['library_name'];
    $address = $_POST['address'];
    // Add other fields as needed (e.g., contact details, library hours)

    // Update library information
    $sql = "UPDATE libraries SET library_name = ?, address = ? WHERE library_id = ?"; // Add other fields as needed
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $library_name, $address, $library_id); // Add other fields as needed
    if ($stmt->execute()) {
        // Library information updated successfully
        header('Location: library_info.php'); // Redirect to library info page
        exit;
    } else {
        // Error updating library information
        echo "Error updating library information"; // Replace with proper error handling
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Information</title>
</head>
<style>

body {
  font-family: Arial, sans-serif;
  margin: 20px;
  font-family: Arial, sans-serif;
  align-items: center;
  background: linear-gradient(to right, #f27121, #e94057);
}

.container {
  max-width: 400px;
  margin: 0 auto;
  padding: 20px;
  border: 1px solid #ccc;
  border-radius: 20px;
  background-color: #f0f0f0;
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

input[type="text"] {
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
  max-width:   
 400px;
  padding: 20px;
  border: 1px solid #ccc;   

  border-radius: 5px;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
  background-color: #fff;
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
input[type="password"]   
 {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

#address {
  height: 60px; /* Increase height of address input */
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
  background-color:   
 #45a049;
}
</style>
<body>
    <div class="container">
    <h2>Library Information</h2>
    <form method="post">
        <input type="hidden" name="library_id" value="<?php echo $library['library_id']; ?>">
        <label for="library_name">Library Name:</label>
        <input type="text" id="library_name" name="library_name" value="<?php echo $library['library_name']; ?>">
        <label for="address">Address:</label>
        <input type="text" id="address" name="address" value="<?php echo $library['address']; ?>">
        <button type="submit">Update Information</button>
    </form>
</div>
</body>
<div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
</html>
