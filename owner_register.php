<?php

include 'includes/db_connect.php'; // Include your database connection file

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $library_name = $_POST['library_name'];
    $library_address = $_POST['library_address'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    // Basic input validation (add more robust validation as needed)
    if (empty($name) || empty($library_name) || empty($library_address) || empty($password) || empty($email)) {
        echo "All fields are required";
    } else {
            // Check for duplicate email
            $check_email_sql = "SELECT * FROM owners WHERE email = ?";
            $check_email_stmt = $conn->prepare($check_email_sql);
            $check_email_stmt->bind_param("s", $email);
            $check_email_stmt->execute();
            $check_email_result = $check_email_stmt->get_result();

            if ($check_email_result->num_rows > 0) {
                echo "Email already exists.";
            } else {
                // Hash the password for security
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Begin transaction
                $conn->begin_transaction();

                // Insert into owners table and get the inserted owner ID
                $owner_sql = "INSERT INTO owners (name, password, email) VALUES (?, ?, ?)";
                $owner_stmt = $conn->prepare($owner_sql);
                $owner_stmt->bind_param("sss", $name, $hashed_password, $email);

                if (!$owner_stmt->execute()) {
                    $conn->rollback();
                    echo "Error inserting owner: " . $owner_stmt->error;
                    exit;
                }

                $owner_id = $owner_stmt->insert_id;

                // Insert into libraries table, using the obtained owner ID
                $library_sql = "INSERT INTO libraries (library_name, address, owner_id) VALUES (?, ?, ?)";
                $library_stmt = $conn->prepare($library_sql);
                $library_stmt->bind_param("ssi", $library_name, $library_address, $owner_id);

                if (!$library_stmt->execute()) {
                    $conn->rollback();
                    echo "Error inserting library: " . $library_stmt->error;
                    exit;
                }

                $library_id = $library_stmt->insert_id;

                // Update the owner table with the library ID
                $update_owner_sql = "UPDATE owners SET library_id = ? WHERE owner_id = ?";
                $update_owner_stmt = $conn->prepare($update_owner_sql);
                $update_owner_stmt->bind_param("ii", $library_id, $owner_id);

                if (!$update_owner_stmt->execute()) {
                    $conn->rollback();
                    echo "Error updating owner: " . $update_owner_stmt->error;
                    exit;
                }

                $conn->commit();
                //echo '<script> alert("Registration successful!");
                 //   window.location.href = "owner/index.php";
                //</script>';

                session_start();
// Store the owner's ID and role in the session
              $_SESSION['user_id'] = $owner_id;
              $_SESSION['role'] = 'owner';
              echo '<script>
              alert("Registration successful!");
              window.location.href = "owner/index.php";
              </script>';
                
                $update_owner_stmt->close();
                $library_stmt->close();
                $owner_stmt->close();
            }
            $check_email_stmt->close();
        
        $check_stmt->close();
        
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Library Owner Registration</title>
</head>
<style>
    body {
  font-family: Arial, sans-serif;
  display: grid;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}

.registration {
  margin-top:30px;
  background-color: #f0f0f0;
  border-radius: 10px;
  box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
  padding: 40px;
  width: 400px;
  text-align: center;
}

h1 {
  margin-bottom: 20px;
}

form {
  display: flex;
  flex-direction: column;
}

label {
  font-weight: bold;
}

input {
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
}

button {
  background-color: #4CAF50;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}

button:hover {
  background-color: #45a049;
}

.error {
  color: red;
  text-align: center;
  margin-bottom: 10px;
}
.home-button-container{
  align:center;
  margin-left:35%;
}
button:disabled {
  background-color: #ccc;
  color: #666;
  cursor: not-allowed;
  opacity: 0.6;
  pointer-events: none;
}
</style>
<script>
    function validatePassword() {
        var password = document.getElementById("password").value;
        var confirmPassword = document.getElementById("cnf-password").value;
        var passwordStatus = document.getElementById("password-status");
        var registerButton = document.getElementById("register-button");
        
        if (password && confirmPassword) {
            if (password !== confirmPassword) {
                passwordStatus.style.color = "red";
                passwordStatus.innerHTML = "Passwords do not match";
                registerButton.disabled = true;
            } else {
                passwordStatus.style.color = "green";
                passwordStatus.innerHTML = "Passwords match";
                registerButton.disabled = false;
            }
        } else {
            passwordStatus.innerHTML = "";
            registerButton.disabled = true;
        }
    }

    function showCurrentTime() {
        var currentTime = document.getElementById("current-time");
        var date = new Date();
        var hours = date.getHours();
        var minutes = date.getMinutes();
        var seconds = date.getSeconds();
        currentTime.innerHTML = "Current Time: " + hours + ":" + minutes + ":" + seconds;
    }

    setInterval(showCurrentTime, 1000);
</script>
<body>
    <div class="registration">
    <span id="current-time"></span><br>
    <h2>Library Owner Registration</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="name">Full Name:</label>
        <input type="text" id="name" name="name" required><br><br>
        <label for="library_name">Library Name:</label>
        <input type="text" id="library_name" name="library_name" required><br><br>
        <label for="library_address">Library Address:</label>
        <input type="text" id="library_address" name="library_address" required><br><br>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>
        <label for="password">Password:</label>
    <input type="password" id="password" name="password" required oninput="validatePassword()"><br><br>
    <label for="password">Confirm Password: <span id="password-status"></span></label>
    <input type="password" id="cnf-password" name="cnf-password" required oninput="validatePassword()"><br><br>
    <span id="password-status"></span>
    
    <button id="register-button" type="submit" disabled>Register</button>
    </form>
</div>
    <div class="home-button-container">
    <p>Already own a Library : <button class="home-button" onclick="location.href='login.php'">Login </button></p>
  </div>
</body>
</html>