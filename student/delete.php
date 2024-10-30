<?php
session_start();

include '../includes/db_connect.php';
if (isset($_GET['delete'])) {
    $complaint_id = filter_var($_GET['delete'], FILTER_VALIDATE_INT);
    if ($complaint_id !== false) {
        // Delete the complaint
        $sql = "DELETE FROM complaints WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $stmt->close(); // Close the statement

        // Redirect back to the complaints page
        header('Location: complains.php');
        exit();
    } else {
        // Handle invalid complaint ID
        echo 'Invalid complaint ID';
    }
}
?>