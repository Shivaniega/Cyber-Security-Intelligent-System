<?php
session_start();

// ✅ Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: log.php");
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

// Fetch total complaints
$totalComplaintsQuery = "SELECT COUNT(*) AS total FROM complaint";
$totalComplaints = $conn->query($totalComplaintsQuery)->fetch_assoc()['total'] ?? 0;

// Fetch rejected complaints
$rejectedComplaintsQuery = "SELECT COUNT(*) AS rejected FROM complaint WHERE status = 'rejected'";
$rejectedComplaints = $conn->query($rejectedComplaintsQuery)->fetch_assoc()['rejected'] ?? 0;

// Fetch solved complaints
$neutralizedComplaintsQuery = "SELECT COUNT(*) AS neutralized FROM complaint WHERE status = 'neutralized'";
$neutralizedComplaints = $conn->query($neutralizedComplaintsQuery)->fetch_assoc()['neutralized'] ?? 0;

// Fetch feedback count
$feedbackQuery = "SELECT COUNT(*) AS total_feedback FROM feedback";
$feedbackCount = $conn->query($feedbackQuery)->fetch_assoc()['total_feedback'] ?? 0;


// ✅ Fetch data from feedback table
$feedback_sql = "SELECT name, email, category, rating, priority, message FROM feedback";
$feedback_result = $conn->query($feedback_sql);

// ✅ Fetch data from complaints table
$complaints_sql = "SELECT issue_id, uname, email, category, complaint FROM complaint";
$complaints_result = $conn->query($complaints_sql);

// ✅ Fetch data from users table
$users_sql = "SELECT fname, lname, email, username, pwd FROM details";
$users_result = $conn->query($users_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>CyberShield Admin Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Base Styles */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: #0a1929;
      color: #e0e0e0;
      overflow-x: hidden;
      min-height: 100vh;
    }

    .container {
      display: flex;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      width: 250px;
      background-color: #0c2135;
      padding: 20px 0;
      border-right: 1px solid #1e3a5c;
      box-shadow: 0 0 15px rgba(0, 80, 255, 0.1);
      z-index: 10;
      transition: all 0.3s ease;
    }

    .logo {
      padding: 20px;
      text-align: center;
      border-bottom: 1px solid #1e3a5c;
      margin-bottom: 20px;
    }

    .logo h1 {
      font-size: 24px;
      color: #4dabf7;
      text-transform: uppercase;
      letter-spacing: 2px;
    }

    .menu {
      list-style: none;
    }

    .menu-item {
      padding: 15px 25px;
      display: flex;
      align-items: center;
      cursor: pointer;
      transition: all 0.3s;
      border-left: 4px solid transparent;
    }

    .menu-item:hover,
    .menu-item.active {
      background-color: #132f4c;
      border-left: 4px solid #4dabf7;
    }

    .menu-item i {
      margin-right: 10px;
      color: #4dabf7;
      width: 20px;
      text-align: center;
    }

    /* Main Content */
    .main-content {
      flex: 1;
      padding: 20px;
      display: flex;
      flex-direction: column;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 1px solid #1e3a5c;
    }

    .header h2 {
      font-size: 24px;
      color: #4dabf7;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-info img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      border: 2px solid #4dabf7;
      object-fit: cover;
    }

    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .card {
      background: linear-gradient(145deg, #132f4c, #0c2135);
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 80, 255, 0.15);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .card-title {
      font-size: 16px;
      color: #a0b3c6;
    }

    .card-icon {
      width: 40px;
      height: 40px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: rgba(77, 171, 247, 0.2);
      color: #4dabf7;
    }

    .card-value {
      font-size: 28px;
      font-weight: bold;
      color: #fff;
      margin-bottom: 5px;
    }

    .card-footer {
      font-size: 14px;
      color: #4dabf7;
    }

    /* Animated Security Visualization */
    .security-visualization {
      width: 100%;
      height: 300px;
      margin: 30px 0;
      position: relative;
      background: linear-gradient(145deg, #0c2135, #0a1929);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 0 20px rgba(77, 171, 247, 0.3);
    }

    .center-shield {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 100px;
      height: 100px;
      background: rgba(77, 171, 247, 0.1);
      border: 2px solid #4dabf7;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: pulse 2s infinite;
    }

    .center-shield i {
      font-size: 40px;
      color: #4dabf7;
    }

    .orbiting-element {
      position: absolute;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background: #4dabf7;
      top: 50%;
      left: 50%;
      transform-origin: 0 0;
    }

    .orbit {
      position: absolute;
      top: 50%;
      left: 50%;
      border: 1px solid rgba(77, 171, 247, 0.3);
      border-radius: 50%;
      transform: translate(-50%, -50%);
    }

    .data-point {
      position: absolute;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #4dabf7;
      animation: blink 3s infinite;
    }

    .connection-line {
      position: absolute;
      height: 1px;
      background: linear-gradient(90deg, #4dabf7, transparent);
      transform-origin: 0 0;
    }

    .threat {
      position: absolute;
      width: 12px;
      height: 12px;
      background: #f44336;
      border-radius: 50%;
      animation: threat-move 10s infinite;
    }

    .defense {
      position: absolute;
      width: 10px;
      height: 10px;
      background: #4caf50;
      border-radius: 50%;
      animation: defense-rotate 8s infinite linear;
    }

    @keyframes pulse {
      0%, 100% { transform: translate(-50%, -50%) scale(1); }
      50% { transform: translate(-50%, -50%) scale(1.05); }
    }

    @keyframes blink {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.3; }
    }

    @keyframes threat-move {
      0% { transform: translate(0, 0); }
      25% { transform: translate(200px, 150px); }
      50% { transform: translate(40px, 250px); }
      75% { transform: translate(-180px, 100px); }
      100% { transform: translate(0, 0); }
    }

    @keyframes defense-rotate {
      0% { transform: rotate(0deg) translateX(120px) rotate(0deg); }
      100% { transform: rotate(360deg) translateX(120px) rotate(-360deg); }
    }

    /* Data Sections */
    .data-section {
      display: none;
      background: linear-gradient(145deg, #132f4c, #0c2135);
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .data-section.active {
      display: block;
    }

    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 1px solid #1e3a5c;
    }

    .section-title {
      font-size: 20px;
      color: #4dabf7;
    }

    .data-table {
      width: 100%;
      border-collapse: collapse;
    }

    .data-table th,
    .data-table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #1e3a5c;
    }

    .data-table th {
      background-color: rgba(77, 171, 247, 0.1);
      color: #4dabf7;
      font-weight: 600;
    }

    .data-table tr:hover {
      background-color: rgba(255, 255, 255, 0.05);
    }

    .status {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }

    .status-open {
      background-color: rgba(255, 193, 7, 0.2);
      color: #ffc107;
    }

    .status-pending {
      background-color: rgba(255, 152, 0, 0.2);
      color: #ff9800;
    }

    .status-resolved {
      background-color: rgba(76, 175, 80, 0.2);
      color: #4caf50;
    }

    .status-critical {
      background-color: rgba(244, 67, 54, 0.2);
      color: #f44336;
    }

    .action-btn {
      padding: 6px 12px;
      background-color: rgba(77, 171, 247, 0.2);
      color: #4dabf7;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: all 0.3s;
    }

    .action-btn:hover {
      background-color: rgba(77, 171, 247, 0.4);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      .sidebar {
        position: fixed;
        left: -250px;
        height: 100vh;
        overflow-y: auto;
      }

      .sidebar.active {
        left: 0;
      }

      .toggle-sidebar {
        display: flex;
      }

      .main-content {
        padding-top: 70px;
      }

      .dashboard-cards {
        grid-template-columns: 1fr;
      }
      
      .data-table {
        display: block;
        overflow-x: auto;
      }

      .security-visualization {
        height: 200px;
      }
    }

    .toggle-sidebar {
      display: none;
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 100;
      background: #4dabf7;
      border: none;
      border-radius: 5px;
      color: white;
      width: 40px;
      height: 40px;
      font-size: 20px;
      cursor: pointer;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    }
   /* Complaint status form */
.status-form {
  display: flex;
  align-items: center;
  gap: 10px;
}

.status-select {
  padding: 6px 10px;
  border-radius: 6px;
  border: 1px solid #4dabf7;
  background: #132f4c;
  color: #e0e0e0;
  font-weight: 500;
  outline: none;
  transition: 0.3s;
}

.status-select:hover {
  border-color: #82c7ff;
  background: #0c2135;
}

.update-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  padding: 6px 12px;
  background: linear-gradient(145deg, #4dabf7, #1a73e8);
  color: #fff;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s, background 0.3s;
}

.update-btn:hover {
  background: linear-gradient(145deg, #1a73e8, #4dabf7);
  transform: scale(1.05);
}

.update-btn i {
  font-size: 14px;
}




 
  </style>
</head>
<body>
  <button class="toggle-sidebar" id="toggleSidebar">
    <i class="fas fa-bars"></i>
  </button>

  <div class="container">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
      <div class="logo">
        <h1>CyberShield</h1>
      </div>
      <ul class="menu">
        <li class="menu-item active" data-target="dashboard">
          <i class="fas fa-chart-line"></i> Dashboard
        </li>
        <li class="menu-item" data-target="complaints">
          <i class="fas fa-comment-dots"></i> Complaints
        </li>
        <li class="menu-item" data-target="feedback">
          <i class="fas fa-comments"></i> Feedback
        </li>
        <li class="menu-item" data-target="users">
          <i class="fas fa-users"></i> Users
        </li>
       
        
      </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <header class="header">
        <h2>Admin Dashboard</h2>
        <div class="user-info">
          <img src="profile-pic.png" alt="Admin Profile" />
          <span><?php echo htmlspecialchars($_SESSION['admin']); ?></span>
          <form method="post" action="logout.php" style="margin-left: 15px;">
            <button type="submit" title="Logout" style="background:#ff4c4c; padding:6px 10px; border:none; border-radius:6px; color:#fff; font-weight:600; cursor:pointer;">Logout</button>
          </form>
        </div>
      </header>

      <!-- Dashboard Section -->
      <section id="dashboard" class="data-section active">
        <div class="dashboard-cards">
          <div class="card">
            <div class="card-header">
              <span class="card-title">Total Complaints</span>
              <div class="card-icon"><i class="fas fa-clipboard-list"></i></div>
            </div>
            <div class="card-value"><?php echo $totalComplaints; ?></div>
            <div class="card-footer">All submitted complaints</div>
          </div>

          <div class="card">
            <div class="card-header">
              <span class="card-title">Rejected Complaints</span>
              <div class="card-icon"><i class="fas fa-times-circle"></i></div>
            </div>
            <div class="card-value"><?php echo $rejectedComplaints; ?></div>
            <div class="card-footer">Complaints rejected by Admin</div>
          </div>

          <div class="card">
            <div class="card-header">
              <span class="card-title">Solved Complaints</span>
              <div class="card-icon"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="card-value"><?php echo $neutralizedComplaints; ?></div>
            <div class="card-footer">Complaints resolved</div>
          </div>

          <div class="card">
            <div class="card-header">
              <span class="card-title">Feedbacks Received</span>
              <div class="card-icon"><i class="fas fa-comments"></i></div>
            </div>
            <div class="card-value"><?php echo $feedbackCount; ?></div>
            <div class="card-footer">Feedback from users</div>
          </div>
        </div>

        <!-- Security Visualization -->
        <div class="security-visualization">
          <div class="center-shield">
            <i class="fas fa-shield-alt"></i>
          </div>

          <div class="orbit orbit1">
           <div class="orbit" style="width: 240px; height: 240px;"></div>
          <div class="orbit" style="width: 180px; height: 180px;"></div>
          <div class="orbit" style="width: 120px; height: 120px;"></div>
          
          </div>
          <div class="orbit orbit2">
              <div class="connection-line" style="top: 150px; left: 180px; width: 70px; transform: rotate(45deg);"></div>
          <div class="connection-line" style="top: 80px; left: 100px; width: 90px; transform: rotate(120deg);"></div>
          <div class="connection-line" style="top: 200px; left: 250px; width: 60px; transform: rotate(75deg);"></div>
          </div>
          <!-- Connection Lines -->
          <div class="connection-line" style="top: 150px; left: 180px; width: 70px; transform: rotate(45deg);"></div>
          <div class="connection-line" style="top: 80px; left: 100px; width: 90px; transform: rotate(120deg);"></div>
          <div class="connection-line" style="top: 200px; left: 250px; width: 60px; transform: rotate(75deg);"></div>
        <!-- Threats -->
          <div class="threat" style="top: 100px; left: 200px;"></div>
          <div class="threat" style="top: 180px; left: 80px;"></div>
          
          <!-- Defenses -->
          <div class="defense" style="top: 50%; left: 50%;"></div>
          <div class="defense" style="top: 50%; left: 50%; animation-delay: -4s;"></div>
          <!-- Random data points -->
              <div class="data-point" style="top: 50px; left: 100px;"></div>
          <div class="data-point" style="top: 80px; left: 250px;"></div>
          <div class="data-point" style="top: 200px; left: 180px;"></div>
          <div class="data-point" style="top: 150px; left: 50px;"></div>
          <div class="data-point" style="top: 250px; left: 280px;"></div>
        </div>
      </section>


<!-- Complaints Section -->
<div id="complaints" class="data-section">
  <div class="section-header">
    <h3 class="section-title">Complaints</h3>
  </div>
  <table class="data-table">
    <thead>
      <tr>
        <th>ISSUE_ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Category</th>
        <th>Complaint</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      <?php
if ($complaints_result->num_rows > 0) {
    while($row = $complaints_result->fetch_assoc()) {
        echo "<tr>
                <td>".$row['issue_id']."</td>
                <td>".$row['uname']."</td>
                <td>".$row['email']."</td>
                <td>".$row['category']."</td>
                <td>".$row['complaint']."</td>
               <td>
  <form method='post' action='update_status.php' class='status-form'>
    <select name='status' class='status-select'>
      <option value='complaint_raised' ".($row['status'] == 'complaint_raised' ? 'selected' : '').">Complaint Raised</option>
      <option value='rejected' ".($row['status'] == 'rejected' ? 'selected' : '').">Rejected</option>
      <option value='neutralized' ".($row['status'] == 'neutralized' ? 'selected' : '').">Neutralized</option>
    </select>
    <input type='hidden' name='issue_id' value='".$row['issue_id']."'>
    <button type='submit' name='update_status' class='update-btn'>
      <i class='fas fa-sync-alt'></i> Update
    </button>
  </form>
</td>

              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No complaints found</td></tr>";
}
?>

    </tbody>
  </table>
</div>


      <!-- Feedback Section -->
      <div id="feedback" class="data-section">
        <div class="section-header">
          <h3 class="section-title">Feedback</h3>
        </div>
        <table class="data-table">
          <thead>
            <tr>
            
              <th>Name</th>
              <th>Email</th>
              <th>Category</th>
              <th>Rating</th>
              <th>Priority</th>
              <th>Message</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($feedback_result->num_rows > 0) {
                while($row = $feedback_result->fetch_assoc()) {
                    echo "<tr>
                           
                            <td>".$row['name']."</td>
                            <td>".$row['email']."</td>
                            <td>".$row['category']."</td>
                            <td>".$row['rating']."</td>
                            <td>".$row['priority']."</td>
                            <td>".$row['message']."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7'>No feedback found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <!-- Users Section -->
      <div id="users" class="data-section">
        <div class="section-header">
          <h3 class="section-title">Users</h3>
        </div>
        <table class="data-table">
          <thead>
            <tr>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
              <th>Username</th>
              <th>Password</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($users_result->num_rows > 0) {
                while($row = $users_result->fetch_assoc()) {
                    echo "<tr>
                            <td>".$row['fname']."</td>
                            <td>".$row['lname']."</td>
                            <td>".$row['email']."</td>
                            <td>".$row['username']."</td>
                            <td>".$row['pwd']."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No users found</td></tr>";
            }
            
            // Close the database connection
            $conn->close();
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <script>
    // Sidebar toggle
    document.getElementById('toggleSidebar').addEventListener('click', () => {
      document.getElementById('sidebar').classList.toggle('active');
    });

    // Menu navigation
    const menuItems = document.querySelectorAll('.menu-item');
    const dataSections = document.querySelectorAll('.data-section');

    menuItems.forEach(item => {
      item.addEventListener('click', () => {
        const target = item.getAttribute('data-target');
        
        if (target) {
          // Update active menu item
          menuItems.forEach(i => i.classList.remove('active'));
          item.classList.add('active');
          
          // Show corresponding section
          dataSections.forEach(section => {
            section.classList.remove('active');
            if (section.id === target) {
              section.classList.add('active');
            }
          });
        }
      });
    });

    // Logout functionality
    document.getElementById('logoutBtn').addEventListener('click', () => {
      window.location.href = 'logout.php'; // Create this file to handle logout
    });
  </script>
</body>
</html>