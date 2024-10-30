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
$library_id = get_library_id($owner_id);

// Get the complaint ID and response from the form
$complaint_id = $_POST['complaint_id'];
$response = $_POST['response'];

// Update the complaint status and add the response
$sql = "UPDATE complaints SET response = ? WHERE id = ? AND library_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $response, $complaint_id, $library_id);
$stmt->execute();

// Check if the update was successful
if ($stmt->affected_rows > 0) {
    header('Location: complaints.php');
    exit();
} else {
    echo "Error responding to complaint.";
}

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