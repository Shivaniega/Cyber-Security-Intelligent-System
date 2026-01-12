<?php
session_start();

// Get posted email and password
$email = $_POST['email'];
$pwd = $_POST['pwd'];

// DB credentials
$servername = "sql300.infinityfree.com";
$username   = "if0_39854533";
$password   = "gajjalavarun";
$database   = "if0_39854533_login";

// Connect to DB
$con = new mysqli($servername, $username, $password, $database);
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Check if credentials exist in 'details' table
$sql = "SELECT * FROM details WHERE email = ? AND pwd = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("ss", $email, $pwd);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // ✅ Credentials matched
    if ($email === "admin@gmail.com" && $pwd === "admin123") {
        $_SESSION['admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $_SESSION['email'] = $email;  // ✅ This is what profile.php should check
        header("Location: main.html");  // or profile.php if you want direct profile
        exit();
    }
} else {
    // ❌ Invalid credentials
    echo "<script>
            alert('❌ Invalid email or password!');
            window.location.href = 'log.html';
          </script>";
    exit();
}

$con->close();
?>


