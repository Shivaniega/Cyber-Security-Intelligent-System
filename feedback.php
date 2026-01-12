<?php
// ✅ Collect form data safely
$name     = $_POST['name'] ?? '';
$email    = $_POST['email'] ?? '';
$category = $_POST['category'] ?? '';
$rating   = $_POST['rating'] ?? '';
$priority = $_POST['priority'] ?? '';
$message  = $_POST['message'] ?? '';

// ✅ Database connection
$servername = "sql300.infinityfree.com";
$username   = "if0_39854533";
$password   = "gajjalavarun";
$database   = "if0_39854533_login";

$conn = new mysqli($servername, $username, $password, $database);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ✅ Insert query with prepared statement (no uid)
$sql = "INSERT INTO feedback (name, email, category, rating, priority, message) 
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

/*
   Bind parameters
   👉 If rating is INT → use "i"
   👉 If rating is VARCHAR → use "s"
*/

// Assuming rating is INT:
$stmt->bind_param("sssiss", $name, $email, $category, $rating, $priority, $message);

// If rating is VARCHAR instead, use:
// $stmt->bind_param("ssssss", $name, $email, $category, $rating, $priority, $message);

if ($stmt->execute()) {
    // ✅ Redirect after success
    header("Location: feedback.html");
    exit();
} else {
    echo "<h1>❌ Feedback not saved</h1>";
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
