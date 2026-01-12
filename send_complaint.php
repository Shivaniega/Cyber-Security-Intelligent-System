
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoloader

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $uname = $_POST['uname'];
    $email = $_POST['email'];
    $category = $_POST['category'];
    $complaint = $_POST['complaint'];
    $details = $_POST['details'] ?? '';
    $document = "";

// Handle file upload
if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $document = $target_dir . basename($_FILES["document"]["name"]);
    move_uploaded_file($_FILES["document"]["tmp_name"], $document);
}
    
    // Generate unique issue ID (W-TID)
    $datePart = date('Ymd');
    $randomPart = strtoupper(bin2hex(random_bytes(3))); // 6-character random string
    $issueId = "W-TID-" . $datePart . "-" . $randomPart;

    // === 1. Save to Database ===
    $servername = "sql300.infinityfree.com";
    $dbuser     = "if0_39854533";
    $dbpass     = "gajjalavarun";
    $database   = "if0_39854533_login";

    $con = new mysqli($servername, $dbuser, $dbpass, $database);

    if ($con->connect_error) {
        die("❌ DB Connection failed: " . $con->connect_error);
    }

    // Use prepared statement (safer)
  $stmt = $con->prepare("INSERT INTO complaint(issue_id, uname, email, category, complaint, details, document) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $issueId, $uname, $email, $category, $complaint, $details, $document);


    if (!$stmt->execute()) {
        die("❌ Database insert failed: " . $stmt->error);
    }

    $stmt->close();
    $con->close();

    // === 2. Send Confirmation Email ===
    $mail = new PHPMailer(true);

    try {
        // SMTP (Brevo)
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '966b3c001@smtp-brevo.com';
        $mail->Password   = 'mXwvV3cqNtLBI8Sy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & recipients
        $mail->setFrom('cubetillu@gmail.com', 'Cyber Security Agent');
        $mail->addAddress($email);              // confirmation to user
        $mail->addAddress('demo01476@gmail.com'); // copy to you

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Complaint Confirmation - Issue ID: ' . $issueId;
        $mail->Body    = "
            <h2>Complaint Received</h2>
            <p>Dear " . htmlspecialchars($uname) . ",</p>
            <p>We have received your complaint and assigned it the following tracking ID:</p>
            <h3 style='color: #3498db;'>" . $issueId . "</h3>
            <p><strong>Category:</strong> " . htmlspecialchars($category) . "</p>
            <p><strong>Complaint Details:</strong></p>
            <blockquote>" . nl2br(htmlspecialchars($complaint)) . "</blockquote>
            <p>Our team will investigate this matter and provide updates via email.</p>
            <p>You can use your Issue ID to track the status of your complaint on our website.</p>
            <p>Thank you for bringing this to our attention,</p>
            <p><strong>Cyber Security Team</strong></p>
        ";

        $mail->send();

        // === 3. Redirect back with success ===
        echo "<script>
                alert('✅ Complaint received! Your Issue ID is: " . $issueId . ". A confirmation email has been sent to $email.');
                window.location.href='Complaint.html';
              </script>";
    } catch (Exception $e) {
        echo "❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    // If accessed directly
    header("Location: Complaint.html");
    exit();
}
?> 