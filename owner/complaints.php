
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

function getStudentNameById($student_id) {
    global $conn;

    $sql = "SELECT name FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['name'];
    } else {
        return null; // Or handle the case where the student ID doesn't exist
    }
}

// Get the complaints for the library
$sql = "SELECT * FROM complaints WHERE library_id = ? ";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $library_id);
$stmt->execute();
$result = $stmt->get_result();

// Display the complaints
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Page</title>
<style>
    body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    font-family: Arial, sans-serif;
    align-items: center;
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
    border: 1px solid #ccc;
    padding: 8px;
    text-align: left;
}

th {
    background-color: #f2f2f2;
}

.respond {
    margin-left:35%;
    margin-top: 20px;
    border:solid 2px;
    border-radius:20px;
    width:400px;
    padding:10px;
    background: lightgreen;
}

label {
    display: block;
    margin-bottom: 5px;
}

input[type="number"], textarea {
    width: 80%;
    padding: 8px;
    border: 1px solid #ccc;
    margin:15px;
}

input[type="submit"] {
    background-color: #4CAF50;
    color: white;
    padding: 12px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-left:35%;
}

.home-button-container {
    text-align: center;
    margin-top: 20px;
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
</head>
<body>
<h2>Complaints<?php //echo $_SESSION['library_name']; ?></h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Student Name</th>
            <th>Subject </th>
            <th>Complaint</th>
            <th> response</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                <td><?php echo $row['id'];?></td>
                    <td><?php echo getStudentNameById($row['student_id']); ?></td>
                    <td><?php echo $row['subject'];?></td>
                    <td><?php echo $row['complaint'];?></td>
                    <td><?php echo $row['response']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><a href="mark_closed.php?complaint_id=<?php echo $row['id']; ?>">Mark as Closed</a></td>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr>
                <td colspan="5">No complaints found for this library.</td>
            </tr>
            <?php
        }
        ?>
    </tbody>
</table>
<div class="respond">
<h2>Respond to a Complaint</h2>
<form action="respond_to_complaint.php" method="post">
    <label for="complaint_id">Complaint ID:</label>
    <input type="number" name="complaint_id" required>
    <br>
    <label for="response">Response:</label>
    <textarea name="response" required></textarea>
    <br>
    <input type="submit" value="Submit Response">
</form>
    </div>
    
<div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    </div>
</body>
</html>