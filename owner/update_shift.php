<?php
session_start();
include("../includes/db_connect.php");

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];

    if ($user_role === 'owner') {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['update_shift'])) {
                $shift_id = $_POST['shift_id'];
                $new_shift_name = $_POST['shift_name'];

                // Update the shift in the shifts table
                $update_shift_sql = "UPDATE shifts SET shift_name = ? WHERE shift_id = ?";
                $update_shift_stmt = $conn->prepare($update_shift_sql);
                $update_shift_stmt->bind_param("si", $new_shift_name, $shift_id);

                if ($update_shift_stmt->execute()) {
                    // Handle successful update
                    $_SESSION['success'] = "Shift updated successfully!";
                    header('Location: shift.php');
                    exit;
                } else {
                    // Handle update error
                    $_SESSION['error'] = "Error updating shift: " . $update_shift_stmt->error;
                    header('Location: shift.php');
                    exit;
                }
            }
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