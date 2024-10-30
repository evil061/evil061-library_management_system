<?php
session_start();
// Include the database connection file
include '../includes/db_connect.php';
$fee_id= $_GET["id"];
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'owner' && $_SESSION['role'] !== 'student')){
  // Redirect to the login page or an appropriate access denied page
  header('Location: ../loginn.php');
  exit();
}
if ($fee_id=null){
  header('Location: ../login.php');
  exit();
}
$fee_id= $_GET["id"];
$receipts_sql = "SELECT * FROM student_fees WHERE fee_id = '$fee_id'";
        $receipts_result = $conn->query($receipts_sql);
        while ($receipt = $receipts_result->fetch_assoc()) {
                 $student_id = $receipt['student_id'] ;
                 $purpose = $receipt['purpose']; 
                 $month = $receipt['month'] ;
                 $amount_paid = $receipt['amount_paid']; 
                 $payment_date = $receipt['payment_date'] ;
                 $due_date = $receipt['due_date'] ;
                 $payment_status =  $receipt['payment_status'];
                 $remark= $receipt['remark'];     
        }
$student_sql = "SELECT * FROM students WHERE student_id = '$student_id'";
$receipts_result = $conn->query($student_sql);
while ($student = $receipts_result->fetch_assoc()) {
$student_name = $student['name'] ;
}
function number_to_words($num) {
  $ones = array('zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine');
  $teens = array('ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen');
  $tens = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety');
  $hundreds = array('', 'one hundred', 'two hundred', 'three hundred', 'four hundred', 'five hundred', 'six hundred', 'seven hundred', 'eight hundred', 'nine hundred');

  if ($num < 10) {
    return $ones[$num];
  } elseif ($num < 20) {
    return $teens[$num - 10];
  } elseif ($num < 100) {
    return $tens[floor($num / 10)] . ($num % 10 != 0 ? ' ' . $ones[$num % 10] : '');
  } elseif ($num < 1000) {
    return $hundreds[floor($num / 100)] . ($num % 100 != 0 ? ' and ' . number_to_words($num % 100) : '');
  } elseif ($num < 1000000) {
    return number_to_words(floor($num / 1000)) . ' thousand' . ($num % 1000 != 0 ? ' ' . number_to_words($num % 1000) : '');
  } elseif ($num < 1000000000) {
    return number_to_words(floor($num / 1000000)) . ' million' . ($num % 1000000 != 0 ? ' ' . number_to_words($num % 1000000) : '');
  } elseif ($num < 1000000000000) {
    return number_to_words(floor($num / 1000000000)) . ' billion' . ($num % 1000000000 != 0 ? ' ' . number_to_words($num % 1000000000) : '');
  }
}

$amount_in_words = number_to_words($amount_paid);
// outputs: one thousand

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="html2canvas.min.js"></script>
  <title>Fee Receipt</title>
 
<style>
  body {
  font-family: Arial, sans-serif;
  margin: 0;
  padding: 0;
  text-align: center;
  background-color: #f5f5f5; /* Set a light background for the body */
}

.certificate {
 object-fit:fill;
  display:inline-block;
  border: 1px solid #ddd;
  margin:0;
  padding: 40px;
  background-image: url("background-image.png");
  background-size: 100% 100%; /* Image will scale to fit within the container */
  background-repeat: no-repeat;
  background-position: center; 
  opacity: 0.9;
  background-color: #fff; /* White background for the certificate */
  border-radius: 5px;
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
}

.top-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.institute-logo {
  margin-top:20px;
  width: 20mm; /* 20mm in pixels */
  height: 20mm;
}

.top-section h1 {
  font-size: 27px; /* Increased font size for the certificate title */
  font-weight: bold; /* Make the title bold */
  margin: 0;
  color: #333; /* Dark gray color for the title */
}

.top-section h2 {
  font-size: 120px;
  margin: 0;
  color: #666; /* Lighter gray color for the subtitle */
}

.certificate-body p {
  font-size: 6px; /* Increased font size for the content */
  line-height: 1.4;
  margin-bottom: 15px;
  color: #444; /* Darker gray color for the content */
}

.name{
  font-size: 1.2em;
}
.course {
  font-style: italic;
  font-weight: bold; /* Bold the name and course */
  font-size: 22px; /* Slightly larger font size for the name and course */
  color: #007bff; /* Blue color for the name and course */
}

.bottom-section p {
  margin-bottom: 25px;
  margin:10px;
  font-size: 12px; /* Smaller font size for the date */
  color: #333; /* Darker gray color for the date */
}

.signatures {
  /* ... other styles ... */
  width: 100%; 
  display: flex;
  justify-content: center;
  align-items:center;
  margin-bottom:25px;
  
}
.receipt {
  background-image: url('background-image.png');
  background-size: 100% 100%;
  padding: 20px;
  width: 375px;
  border: 1px solid #ccc;
  border-radius: 10px;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
.recipt-line{
  display: flex;
  justify-content:space-evenly;

}
.remark{
  
  margin:"40px";
}
button {
  background-color: #4CAF50; /* green background color */
  color: #fff; /* white text color */
  padding: 10px 20px; /* padding for the button */
  border: none; /* no border */
  border-radius: 5px; /* rounded corners */
  cursor: pointer; /* change cursor to a pointing hand on hover */
}

button:hover {
  background-color: #3e8e41; /* darker green background color on hover */
  color: #fff; /* white text color remains the same on hover */
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.2); /* add a box shadow on hover */
  transform: scale(1.1); /* scale the button up slightly on hover */
  transition: all 0.3s ease-in-out; /* smooth transition effect */
}
</style>
</head>
<body>
  <div class="receipt-container">
    <div class="receipt">
      <img src="header.png" alt="Institute logo" class="institute-logo">
      <div class="receipt-body">
        <h1>Fee Receipt</h1>
        <p>Date/Time : <span id="date"><?php echo $payment_date; ?></span></p>
        <div class= "recipt-line">
        <p>Receipt No: <span id="receipt-no"><?php echo $fee_id; ?></span></p>
        <p>Name: <span id="student-name"><?php echo $student_name; ?> <span></p>
        </div>
        <div class= "recipt-line">
        <p>Amount: <span id="amount"><?php echo $amount_paid; ?></span></p>
        <p>Status: <span id="fee-status"><?php echo $payment_status?></span></p>
        </div>
        <div class= "recipt-line">
        <p>Month: <span id="month"><?php echo $month; ?></span></p>
        <p>Type: <span id="fee-type"><?php echo $purpose; ?></span></p>
        </div>
        <div class= "recipt-line">
        <p>Due Date: <span id="due-date"><?php echo $due_date; ?></span></p>
        </div>
        <div class= "remark">
        <p>Remark: <span id="remark"><?php echo $remark; ?></span></p>
        </div>
      </div>
      <div class="bottom-section">
        <p>Received from '<b><i><span id="payer-name"><?php echo $student_name; ?></i></b></span>' a sum of <span id="amount-in-words"><b><i><?php echo $amount_in_words; ?></i></b></span> only.</p>
        <div class="signatures">
          <p class="signature-left">
            Signature<br>
            Tushar (DEV)<br>
          </p>
        </div>
      </div>
    </div>

  </div>
  <!-- Add this button to your HTML file -->
<button onclick="downloadReceipt()">Download </button>
  <script>
function downloadReceipt() {
  html2canvas(document.body, {
    width: 415,
    height: 610,
    x: 0,
    y: 0
  }).then(canvas => {
    const link = document.createElement('a');
    link.href = canvas.toDataURL();
    link.download = 'fee_receipt.png';
    link.click();
  });
}
  </script>
</body>
</html>