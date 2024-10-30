<?php
session_start();

include '../includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

function getLibraryIdBystudentId($student_id) {
    global $conn;

    $sql = "SELECT library_id FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return "Student not found";
    }

    return $row['library_id'];
}

function getStudentNameByStudentId($student_id) {
    global $conn;

    $sql = "SELECT name FROM students WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        return "Student not found";
    }

    return $row['name'];
}

$student_id = $_SESSION["user_id"];
$library_id = getLibraryIdBystudentId($student_id);
$student_name = getStudentNameByStudentId($student_id);
$role = $_SESSION['role']; // Assuming the role is stored in the session

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $complaint = $_POST['complaint'];
    $subject = $_POST['subject'];
    $status = "pending";
    // Insert the complaint into the complaints table
    $sql = "INSERT INTO complaints (student_id, library_id, subject, name, complaint, status) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissss", $student_id, $library_id, $subject, $student_name, $complaint, $status);
    $stmt->execute();

    // Redirect back to the complaints page
    header('Location: complains.php');
    exit();
}

if (isset($_GET['delete'])) {
  $complaint_id = $_GET['delete'];

  // Delete the complaint
  $sql = "DELETE FROM complaints WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $complaint_id);
  $stmt->execute();

  // Close the statement
  $stmt->close();

  // Redirect back to the complaints page
  header('Location: complaints.php');
  exit();
}

$query = "SELECT * FROM complaints WHERE student_id = '$student_id'";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>
    <title>Complain</title>
</head>
<style>
        body {
          font-family: Arial, sans-serif;
          margin: 20px;
          align-items: center;
          min-height: 100vh;
          background: linear-gradient(to right, #f27121, #e94057);
          display:flex;
        }

        form {
          width: 75%;
          margin: 40px auto;
        }

        label {
          display: block;
          margin-bottom: 10px;
        }

        input[type="text"], textarea {
          width: 100%;
          padding: 10px;
          margin-bottom: 20px;
          border: 1px solid #ccc;
        }

        input[type="submit"] {
          background-color: #f0f0f0;
          color: #333;
          border: 1px solid #ccc;
          padding: 10px 20px;
          border-radius: 4px;
          cursor: pointer;
        }
        .complains{
            margin-left:40px;
            margin-right:60px;
            width:500px;
            border-radius:20px;
            border: solid 1px #ccc;
            background-color:#f0f0f0;

        }
        .home-button-container {
       text-align: center;
       margin-top: 20px;
       position: absolute;
       top:90%;
       left:20%;
       transform: translate(-50%, -50%);
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

    </style>
<body>
    <div class="complains">
        <h1 align="center">Complain</h1>
        <form method="post">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>
            <label for="complaint">Complaint:</label>
            <textarea id="complaint" name="complaint" rows="5" cols="30"></textarea>
            <input type="submit" value="Submit">
        </form>
    </div>
    <div class="existing_complains">
        <h1>Existing Complaints</h1>
        <ul>
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($complaint = mysqli_fetch_assoc($result)) {
                    echo '<li>';
                    echo 'Subject: ' . $complaint['subject'] . '<br>';
                    echo 'Complaint: ' . $complaint['complaint'] . '<br>';
                    echo 'response: ' . $complaint['response'] . '<br>';
                    echo 'Status: ' . $complaint['status'] . '<br>';
                    echo '<a href="delete.php?delete=' . $complaint['id'] . '">Delete</a>';
                    echo '</li>';
                }
            } else {
                echo '<p>No complaints found for this student.</p>';
            }
            mysqli_close($conn);
            ?>
        </ul>
    </div>

</body>
<div class="home-button-container">
    <button class="home-button" onclick="location.href='index.php'">Home</button>
  </div>
</html>