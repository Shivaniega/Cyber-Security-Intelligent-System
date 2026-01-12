<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer autoloader

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $name      = $_POST['name'] ?? '';
    $email     = $_POST['email'] ?? '';
    $spamtype  = $_POST['spamtype'] ?? '';
    $complaint = $_POST['complaint'] ?? '';

    // ✅ Generate unique complaint ID
    $id = time() . random_int(100, 999);

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
    $stmt = $con->prepare("INSERT INTO spam(id, name, email, spamtype, complaint) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $id, $name, $email, $spamtype, $complaint);

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
        $mail->Subject = 'Complaint Confirmation';
        $mail->Body    = "
            <p>Dear " . htmlspecialchars($name) . ",</p>
            <p>We have received your complaint under the category: <b>" . htmlspecialchars($spamtype) . "</b>.</p>
            <p><b>Complaint ID:</b> $id</p>
            <blockquote>" . nl2br(htmlspecialchars($complaint)) . "</blockquote>
            <p>Our team will look into it and get back to you if necessary.</p>
            <p>Thank you,<br>Cyber Security Agent</p>
        ";

        $mail->send();

        // === 3. Redirect back with success ===
        echo "<script>
                alert('✅ Complaint received! A confirmation email has been sent to $email.');
                window.location.href='spam.html';
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
