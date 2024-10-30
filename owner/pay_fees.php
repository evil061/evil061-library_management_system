<?php
    // Connect to the database
    include '../includes/db_connect.php';
    $student_id = $_GET['student_id'];
    //echo $student_id ;

    function getStudentFees($studentId) {
        global $conn;
    
        // Prepare and execute the query
        $sql = "SELECT amount_paid, due_date, payment_status FROM student_fees WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return array(
                'amount_paid' => $row['amount_paid'],
                'due_date' => $row['due_date'],
                'payment_status' => $row['payment_status']
            );
        } else {
            return null; // Student not found or no fees assoated
        }
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

    $student_fees = getStudentFees($student_id);
    ?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Student Fees</title>
    <style>
        body {
  font-family: Arial, sans-serif;
  margin: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(to right, #f27121, #e94057);
}

#fees_container {
  margin-top:40px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 10px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 400px;
}
#reciept_container{
margin-top:40px;
  background-color: rgba(255, 255, 255, 0.8);
  border-radius: 10px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: auto; 
  text-align:center;
}
header {
  
  padding: 0px;
  text-align: center;
  display: flex;
  justify-content: center;
  align-items: center;
}


header h1 {
  font-family: 'Montserrat', sans-serif; /* Replace with your preferred font */
  font-size: 3rem;
  color: #333;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
  animation: fadeInUp 1s ease-in-out;
}
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0); }
}
.submit-button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
}
button:hover {
  background-color: #45a049;
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
input[type="text"],
input[type="date"],
input[type="email"],
input[type="password"],
input[type="hidden"],
select {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}
.home-button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease-in-out;
}
button:hover {
  background-color: red;
}
    </style>
</head>
<body>
    <header><h1>Update Student Fees</h1></header>
    <form id="fees_container" method="post" action= "mark_fees.php">
    <h1 align="center">Mark fees</h1>
    <input type="hidden" name="student_id" value="<?php echo $student_id; ?>" >
        <label for="student_id">Student Name:</label>
        <input type="text" name="student_name" value="<?php echo getStudentName($student_id);?>" disabled ><br><br>
        <?php
$student_fees = getStudentFees($student_id);
if ($student_fees !== null) {
    ?>
    <label for="amount_paid">Amount:</label>
    <input type="text" name="amount_paid" value="" required><br><br>
    <label for="purpose">Fee for:</label>
    <select name="purpose" id="purpose" required>
        <option value="">Select Fee For</option>
        <option value="Library_fee">Library </option>
        <option value="Registration_fee">Registration </option>
        <option value="Fine">Fine </option>
        <option value="other">other</option>
        </select><br><br>
    <label for="month">Select Month:</label>
    <select name="month" id="month" required>
        <option value="">Select Month</option>
        <option value="January">January</option>
        <option value="February">February</option>
        <option value="March">March</option>
        <option value="April">April</option>
        <option value="May">May</option>
        <option value="June">June</option>
        <option value="July">July</option>
        <option value="August">August</option>
        <option value="September">September</option>
        <option value="October">October</option>
        <option value="November">November</option>
        <option value="December">December</option>
    </select><br><br>
    <label for="payment_status">Payment Status:</label>
    <select name="payment_status" required>
        <option value="">Select</option>
        <option value="paid">Paid</option>
        <option value="pending">Pending</option>
        <option value="overdue">Overdue</option>
    </select><br><br>
    <label for="due_date">Due Date:</label>
    <input type="date" name="due_date" value="<?php echo $student_fees['due_date']; ?>" required><br><br>
    <i> Plese  select the date for the due date</i><br>
    <label for="remark">Remark:</label>
    <input type="text" name="remark" value="" placeholder="Remarks" ><br><br>
    <?php
} else {
    ?>
    <label for="amount_paid">Amount :</label>
    <input type="text" name="amount_paid" required><br><br>
    <label for="purpose">Fee for:</label>
    <select name="purpose" id="purpose" required>
        <option value="">Select Fee For</option>
        <option value="Library_fee">Library </option>
        <option value="Registration_fee">Registration </option>
        <option value="Fine">Fine </option>
        <option value="other">other</option>
        </select><br><br>
    <label for="month">Select Month:</label>
    <select name="month" id="month" required>
        <option value="">Select Month</option>
        <option value="January">January</option>
        <option value="February">February</option>
        <option value="March">March</option>
        <option value="April">April</option>
        <option value="May">May</option>
        <option value="June">June</option>
        <option value="July">July</option>
        <option value="August">August</option>
        <option value="September">September</option>
        <option value="October">October</option>
        <option value="November">November</option>
        <option value="December">December</option>
    </select><br><br>
    <label for="payment_status">Payment Status:</label>
    <select name="payment_status" required>
    <option value="">Select</option>
        <option value="paid">Paid</option>
        <option value="pending">Pending</option>
        <option value="overdue">Overdue</option>
    </select><br><br>
    <label for="due_date">Due date:</label>
    <input type="date" name="due_date" required><br><br>
    <i> Plese  select the date for the due date</i><br>
    <label for="remark">Remark:</label>
    <input type="text" name="remark" value="" placeholder="Remarks" ><br><br>
    <?php
}
?>
        <input type="submit" value="Update Fees" class="submit-button">
    </form>


    <div class="home-button-container">
        <button class="home-button" onclick="location.href='index.php'">Home</button>
    
    <button class="home-button" onclick="location.href='student_management.php'">Back</button>
    </div>

    <div id="reciept_container">
    <h1>Receipts</h1>
    <p>Student Name : <?php echo getStudentName($student_id); ?> |  Student ID: <?php echo $student_id ?></p>
    <table>
        <tr>
            <th>Receipt ID</th>
            <th>purpose</th>
            <th>Month</th>
            <th>Amount Paid</th>
            <th>Initiated Date</th>
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


    <script>
    function promptPaymentAmount(feeId, studentId) {
    var amount = prompt("Enter payment amount:");
    if (amount != null && amount != "") {
        window.location.href = "mark_paid.php?fee_id=" + feeId + "&amount=" + amount + "&student_id=" + studentId;
    }
}

</script>
</td>        
            </tr>
            <?php
        }
        ?>
    </table>
</div>

</body>
</html>