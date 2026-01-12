<?php
// Enable CORS if needed (for testing)
// header("Access-Control-Allow-Origin: *");

require 'db.php'; // Your database connection file

$data = json_decode(file_get_contents("php://input"), true);
$message = strtolower(trim($data['message'] ?? ''));
$email = trim($data['email'] ?? '');

if (!$message || !$email) {
    http_response_code(400);
    echo "Missing message or email.";
    exit;
}

// Basic spam keyword check (reuse your JS list or simplify)
$spam_keywords = ['free', 'click here', 'win', 'prize', 'buy now', 'urgent'];
$isSpam = false;

foreach ($spam_keywords as $word) {
    if (strpos($message, $word) !== false) {
        $isSpam = true;
        break;
    }
}

if (!$isSpam) {
    echo "Message not considered spam on backend.";
    exit;
}

// Fetch user phone number
$stmt = $pdo->prepare("SELECT phone_number FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    http_response_code(404);
    echo "User not found.";
    exit;
}

$phone = $user['phone_number'];
$alertMessage = "⚠️ Alert: A suspicious message was detected. Please be cautious.";

// Send SMS via FastSMS / Esendex
$username = 'your_fastsms_username';
$password = 'your_fastsms_password';
$accountRef = 'your_account_ref';
$from = 'SpamShield';

$xml = <<<XML
<messages>
    <accountreference>{$accountRef}</accountreference>
    <message>
        <to>{$phone}</to>
        <from>{$from}</from>
        <body>{$alertMessage}</body>
        <type>SMS</type>
    </message>
</messages>
XML;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.esendex.com/v1.0/messagedispatcher");
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: text/xml",
    "Content-Length: " . strlen($xml)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch);
} else {
    echo "SMS sent successfully!";
}
curl_close($ch);
?>