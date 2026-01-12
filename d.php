<?php
session_start();

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: log.html");
    exit();
}

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

// ✅ Fetch data from feedback/complaints table
$sql = "SELECT id, uname, email,category, complaint FROM complaint";  // change table/columns as per your DB
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #333; color: #fff; }
    </style>
</head>
<body>
    <h2>📋 Admin Dashboard - Submitted Data</h2>

    <table>
        <tr>
        <th>id</th>
            <th>uname</th>
            <th>email</th>
            <th>category</th>
            <th>complaint</th>
        </tr>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                 <td>".$row['id']."</td>
                        <td>".$row['name']."</td>
                        <td>".$row['email']."</td>
                        <td>".$row['category']."</td>
                            <td>".$row['complaint']."</td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3'>No data found</td></tr>";
        }
        ?>
    </table>
</body>
</html>
<?php
$conn->close();
?>
