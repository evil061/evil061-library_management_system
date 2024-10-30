<?php
session_start();

include '../includes/db_connect.php';

// Check if the user is logged in as the library owner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'owner') {
    // Redirect to the login page or an appropriate access denied page
    header('Location: ../login.php');
    exit();
}

// Get the complaint ID from the URL
$id = $complaint_id = $_GET['complaint_id'];

// Update the complaint status to closed
$sql = "UPDATE complaints SET status = 'closed' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $complaint_id);
$stmt->execute();

if ($stmt->affected_rows == 1) {
    echo '<script>
        alert("Complaint marked as closed successfully.");
        window.location.href = "complaints.php";
    </script>';
} else {
    echo '<script>
        alert("Failed to mark complaint as closed.");
        window.location.href = "complaints.php";
    </script>';
}