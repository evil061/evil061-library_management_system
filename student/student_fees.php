<?php
session_start();
// Include the necessary PHP file for database connection and functions
include '../includes/db_connect.php';

// Get the student's ID from the session or URL parameter
$student_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : $_GET['user_id'];

// Check if the student ID is valid
if (empty($student_id)) {
    echo "Invalid student ID.";
    exit();
}

// Get the student's fees from the database
$sql = "SELECT * FROM student_fees WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();

$fees = [];
while ($row = $result->fetch_assoc()) {
    $fees[] = $row;
}

// Pass the fees array to the HTML template
//include 'student_fees.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Fee Payment</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Student Fee Payment</h1>

    <?php include 'fetch_fees.php'; ?>

    <div class="fee-container">
        <?php if (!empty($fees)) : ?>
            <?php foreach ($fees as $fee) : ?>
                <div class="fee-card">
                    <h2>Fee ID: <?php echo $fee['fee_id']; ?></h2>
                    <p>Fee Amount: <?php echo $fee['fee_amount']; ?></p>
                    <p>Due Date: <?php echo $fee['due_date']; ?></p>
                    <p>Payment Status: <?php echo $fee['payment_status']; ?></p>
                    <?php if ($fee['payment_status'] === 'pending') : ?>
                        <form action="process_payment.php" method="post">
                            <input type="hidden" name="fee_id" value="<?php echo $fee['fee_id']; ?>">
                            <input type="submit" value="Pay Now">
                        </form>
                    <?php else : ?>
                        <p>Payment already made.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No fees found for this student.</p>
        <?php endif; ?>
    </div>
</body>
</html>