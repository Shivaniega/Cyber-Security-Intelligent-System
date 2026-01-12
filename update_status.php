<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $issueId = isset($_POST['issue_id']) ? trim($_POST['issue_id']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    if (empty($issueId)) {
        die("❌ Invalid Issue ID provided.");
    }
    if (empty($status)) {
        die("❌ Status cannot be empty.");
    }

    $servername = "sql300.infinityfree.com";
    $dbuser     = "if0_39854533";
    $dbpass     = "gajjalavarun";
    $database   = "if0_39854533_login";

    $con = new mysqli($servername, $dbuser, $dbpass, $database);
    if ($con->connect_error) {
        die("❌ Database connection failed: " . $con->connect_error);
    }

    // Fetch uname and email for notification
    $stmt = $con->prepare("SELECT uname, email FROM complaint WHERE issue_id = ?");
    if (!$stmt) {
        die("❌ Prepare failed: " . $con->error);
    }
    $stmt->bind_param("s", $issueId);  // 's' for string
    $stmt->execute();
    $stmt->bind_result($uname, $email);
    $stmt->fetch();
    $stmt->close();

    if (empty($email)) {
        die("❌ No complaint found with Issue ID: $issueId");
    }

    // Update only the status field
    $stmt = $con->prepare("UPDATE complaint SET status = ? WHERE issue_id = ?");
    if (!$stmt) {
        die("❌ Prepare failed (update): " . $con->error);
    }
    $stmt->bind_param("ss", $status, $issueId); // both strings

    if (!$stmt->execute()) {
        die("❌ Failed to update status: " . $stmt->error);
    }
    $stmt->close();
    $con->close();

    echo "<p>✅ Status updated successfully in database.</p>";

    // Send notification email
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug = 0; // Disable debug in production
        $mail->Debugoutput = 'html';

        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '966b3c001@smtp-brevo.com';
        $mail->Password   = 'mXwvV3cqNtLBI8Sy';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('cubetillu@gmail.com', 'Cyber Security Agent');
        $mail->addAddress($email, $uname);

        $mail->isHTML(true);
        $mail->Subject = "Complaint Status Updated - Issue ID: $issueId";
        $mail->Body = "
            <h2>Status Update Notification</h2>
            <p>Dear " . htmlspecialchars($uname) . ",</p>
            <p>Your complaint with Issue ID <strong>$issueId</strong> has been updated to status: <strong>" . htmlspecialchars($status) . "</strong>.</p>
            <p>If you have any questions, please contact our support team.</p>
            <p>Thank you,<br><strong>Cyber Security Team</strong></p>
        ";

        $mail->send();
        echo "<p>✅ Email sent successfully to $email.</p>";
    } catch (Exception $e) {
        echo "<p>❌ Email could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
    }

} else {
    header("Location: admin.php");
    exit();
}
