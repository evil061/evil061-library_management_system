<?php
    // Connect to the database
    include '../includes/db_connect.php';
    $student_id = $_GET['student_id'];
    //echo $student_id ;

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

    $student_name = getStudentName($student_id);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registration Fee</title>
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
            margin-top: 40px;
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 400px;
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
                transform: translateY(0);
            }
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
    </style>
</head>

<body>
    <header><h1>Registration Fee</h1></header>
    <form id="fees_container" method="post" action="register_fee_submit.php">
        <h1 align="center">Registration Fee</h1>
        <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
        <label for="student_name">Student Name:</label>
        <input type="text" name="student_name" value="<?php echo $student_name; ?>" disabled><br><br>
        <label for="amount_paid">Amount:</label>
        <input type="text" name="amount_paid" required><br><br>
        <label for="month">Select Month:</label>
        <select name="month" id="month" required>
            <option value="">Select Month</option>
            <option value="January">January</option>
            <option value="February">February</option>
            <option value="March">March</option>
            <option value="April">April</option>
            <option value="May">May</option>
            <option value="June">June</option>
            <option value="July">July</ option>
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
        <input type="submit" class="submit-button" value="Submit">
    </form>
</body>
</html>