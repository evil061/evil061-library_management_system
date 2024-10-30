<?php
session_start();

include '../includes/db_connect.php';

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];

// Get the library ID of the logged in owner
function get_library_id($owner_id) {
    global $conn;
    $sql = "SELECT library_id FROM libraries WHERE owner_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['library_id'];
}

$library_id = get_library_id($owner_id);

// Handle form submission for posting a new notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];

    // Insert the new notice into the notifications table
    $sql = "INSERT INTO notifications (owner_id, library_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $owner_id, $library_id, $message);
    $stmt->execute();

    // Redirect back to the notice page after successful insertion
    header('Location: notice.php');
    exit();
}

// Handle form submission for editing an existing notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_notice_id']) && isset($_POST['edited_message'])) {
    $notice_id = $_POST['edit_notice_id'];
    $edited_message = $_POST['edited_message'];

    // Update the existing notice in the notifications table
    $sql = "UPDATE notifications SET message = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $edited_message, $notice_id);
    $stmt->execute();

    // Redirect back to the notice page after successful update
    header('Location: notice.php');
    exit();
}

// Handle form submission for deleting a notice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notice_id'])) {
    $notice_id = $_POST['delete_notice_id'];

    // Delete the notice from the notifications table
    $sql = "DELETE FROM notifications WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $notice_id);
    $stmt->execute();

    // Redirect back to the notice page after successful deletion
    header('Location: notice.php');
    exit();
}

// Retrieve existing notices for the library owner
$sql = "SELECT * FROM notifications WHERE owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Library Owner Dashboard - Notices</title>
</head>
<style>
body {
    font-family: Arial, sans-serif;
  margin: 40px;
  margin-right:30px;
  display: flex;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(to right, #f27121, #e94057);
}

h2 {
    text-align: center;
    margin-bottom: 20px;
}

table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 20px;
}

th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

th {
    background-color: BLACK;
    color: white;
}

form {
    margin-bottom: 20px;
}

label {
    display: block;
    margin-bottom: 5px;
}

textarea {
    width: 100%;
    height: 100px;
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
  background-color: lightgreen;
}
.container {
  margin-top:0px;
  margin-bottom:30px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 10px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 400px;
}
.container2 {
  margin-top:0px;
  margin-left:30px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 10px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 700px;
}
.home-button-container {
    text-align: center;
    margin-top: 20px;
    margin-left:30px;
}

.home-button {
    background-color: #f2f2f2;
    border: none;
    color: black;
    padding: 12px 20px;
    border-radius: 4px;
    cursor: pointer;
}
</style>
<body>
    <div class="container">
    <h2>Notices</h2>

    <form action="notice.php" method="post">
        <label for="message">Message:</label>
        <textarea name="message" required></textarea>
        <br>
        <input type="submit" value="Post Notice">
    </form>
</div>
<div class ="container2">
    <table>
        <thead>
            <tr>
                <th>Notice</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    ?>
                    <tr>
                        <td><?php echo $row['message']; ?></td>
                        <td>
                            <form action="notice.php" method="post">
                                <input type="hidden" name="edit_notice_id" value="<?php echo $row['id']; ?>">
                                <input type="text" name="edited_message" value="<?php echo $row['message']; ?>">
                                <input type="submit" value="Edit">
                            </form>
                            
                            <form action="notice.php" method="post">
                                <input type="hidden" name="delete_notice_id" value="<?php echo $row['id']; ?>">
                                <input type="submit" value="Delete">
                            </form>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="2">No notices found for this library.</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
        </div>
        
</body>
<div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
</html>