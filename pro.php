<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: log.php");
    exit();
}

$email = $_SESSION['email'];

// Database connection
$servername = "sql300.infinityfree.com";
$dbuser     = "if0_39854533";
$dbpass     = "gajjalavarun";
$database   = "if0_39854533_login";

$conn = new mysqli($servername, $dbuser, $dbpass, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user details from details table
$sqlDetails = "SELECT fname, lname, email, username FROM details WHERE email = ?";
$stmtDetails = $conn->prepare($sqlDetails);
$stmtDetails->bind_param("s", $email);
$stmtDetails->execute();
$resultDetails = $stmtDetails->get_result();

if ($resultDetails->num_rows == 0) {
    echo "User details not found.";
    exit();
}

$user = $resultDetails->fetch_assoc();

// Fetch complaints from complaint table
$sqlComplaints = "SELECT issue_id, uname, email, category, complaint, status FROM complaint WHERE email = ?";
$stmtComplaints = $conn->prepare($sqlComplaints);
$stmtComplaints->bind_param("s", $email);
$stmtComplaints->execute();
$resultComplaints = $stmtComplaints->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>User Profile</title>
    <link rel="stylesheet" href="cyber-style.css" />

    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; }
        h1, h2 { color: #333; }
        table { border-collapse: collapse; width: 100%; margin-top: 10px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .profile-info { margin-bottom: 30px; }
    </style>
</head>
<body>

<h1>Welcome, <?php echo htmlspecialchars($user['fname'] . " " . $user['lname']); ?>!</h1>

<div class="profile-info">
    <h2>Your Details</h2>
    <p><strong>First Name:</strong> <?php echo htmlspecialchars($user['fname']); ?></p>
    <p><strong>Last Name:</strong> <?php echo htmlspecialchars($user['lname']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
</div>

<div class="complaints">
    <h2>Your Complaints</h2>
    <?php if ($resultComplaints->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Issue ID</th>
                    
                    <th>Category</th>
                    <th>Complaint</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($complaint = $resultComplaints->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($complaint['issue_id']); ?></td>
                 
                    <td><?php echo htmlspecialchars($complaint['category']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['complaint']); ?></td>
                    <td><?php echo htmlspecialchars($complaint['status']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have not filed any complaints yet.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmtDetails->close();
$stmtComplaints->close();
$conn->close();
?>
