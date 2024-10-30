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
function getStudentName($student_id) {
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
        return null;
    }
}

// Pass the fees array to the HTML template
//include 'student_fees.php';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fee Payment</title>
    
    <style>
        body {

font-family: Arial, sans-serif;
margin-left : 20px;
padding:0px;
display: flex;

align-items: center;
min-height:;
background: linear-gradient(to right, #f27121, #e94057);
}

            table {
      border-collapse: collapse; /* Ensures borders don't overlap */
      width: 100%; /* Makes the table full width */
    }

    th, td {
      padding: 10px;
      border: 1px solid #ccc;
      text-align: left; /* Aligns content to the left */
    }

    th {
      background-color: #f0f0f0; /* Light gray background for headers */
      font-weight: bold; /* Makes headers stand out */
    }
</style>
</head>
<body>
<div id="reciept_container">
    <h1>Receipts</h1>
    <p>Student Name : <?php echo getStudentName($student_id); ?> |  Student ID: <?php echo $student_id ?></p>
    <table>
        <tr>
            <th>Receipt ID</th>
            <th>Purpose</th>
            <th>Month</th>
            <th>Amount Paid</th>
            <th>Payment Date / Time</th>
            
            <th>Due Date</th>
            <th>Payment Status</th>
            <th>Action</th>
            
        </tr>
        <?php
        $receipts_sql = "SELECT * FROM student_fees WHERE student_id = '$student_id'";
        $receipts_result = $conn->query($receipts_sql);
        while ($receipt = $receipts_result->fetch_assoc()) {
            ?>
            <tr>
                <td><?php echo $receipt['fee_id'] ?></td>
                <td><?php echo $receipt['purpose'] ?></td>
                <td><?php echo $receipt['month'] ?></td>
                <td><?php echo $receipt['amount_paid'] ?></td>
                <td><?php echo $receipt['payment_date'] ?></td>
               
                <td><?php echo $receipt['due_date'] ?></td>
                <td><?php echo $receipt['payment_status'] ?></td>
                <td>
                <?php if ($receipt['payment_status'] == 'pending' || $receipt['payment_status'] == 'overdue') { ?>
                <button class="home-button" onclick="promptPaymentAmount(<?php echo $receipt['fee_id']; ?>, <?php echo $student_id; ?>)">Pay</button>
                <?php } elseif ($receipt['payment_status'] == 'paid') { ?>
                    <a href="../recipt/index.php?id=<?php echo $receipt['fee_id']; ?>" target="_blank">Receipt</a>
                <?php } ?>
                </td>
                
            </tr>
            <?php
        }
        ?>
    </table>
</body>
</html>