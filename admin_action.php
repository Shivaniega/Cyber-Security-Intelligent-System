 <?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';  // PHPMailer autoload

$servername = "sql300.infinityfree.com";
$username   = "if0_39854533";
$password   = "gajjalavarun";
$database   = "if0_39854533_login";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $complaint_id = $_POST['issue_id'];   // corrected name
    $status       = $_POST['status'];

    // Update complaint status (assuming issue_id is string)
    $sql = "UPDATE complaints SET status=? WHERE issue_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $status, $complaint_id);
    $stmt->execute();

    // Fetch email and complaint details
    $stmt2 = $conn->prepare("SELECT email, complaint FROM complaints WHERE issue_id=?");
    $stmt2->bind_param("s", $complaint_id);
    $stmt2->execute();
    $result = $stmt2->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $user_email = $row['email'];
        $issue      = $row['complaint'];

        // Send Email via Brevo SMTP using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp-relay.brevo.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '966b3c001@smtp-brevo.com';   // Brevo SMTP user
            $mail->Password   = 'mXwvV3cqNtLBI8Sy';           // Brevo SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('cubetillu@gmail.com', 'Cyber Security Agent');
            $mail->addAddress($user_email);
            $mail->addAddress('demo01476@gmail.com'); // admin copy

            $mail->isHTML(true);
            $mail->Subject = "Complaint Update (ID: $complaint_id)";
            $mail->Body    = "
                <p>Hello,</p>
                <p>Your complaint <b>$issue</b> has been updated.</p>
                <p><b>New Status:</b> $status</p>
                <p>Regards,<br><b>Cyber Security Agent</b></p>
            ";

            $mail->send();

        } catch (Exception $e) {
            echo "❌ Email not sent: {$mail->ErrorInfo}";
            exit;
        }
    } else {
        echo "❌ Invalid Issue ID provided.";
        exit;
    }

    // Redirect after success
    header("Location: d.php?success=1");
    exit();
}
?>
