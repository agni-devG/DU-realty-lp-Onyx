<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

header('Content-Type: application/json');

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/mail-config.php';

function respond(bool $ok, string $message, int $status = 200): void
{
    http_response_code($status);
    echo json_encode(['ok' => $ok, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

if (!empty($_POST['website'] ?? '')) {
    respond(true, 'Thank you. We will contact you soon.');
}

$name = trim((string) ($_POST['name'] ?? ''));
$contact = trim((string) ($_POST['contact'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$leadType = trim((string) ($_POST['lead_type'] ?? 'site_visit'));

if ($name === '' || $contact === '') {
    respond(false, 'Please enter your name and contact number.', 422);
}

$normalizedContact = preg_replace('/[\s-]/', '', $contact);

if (!preg_match('/^(?:\+91|91)?[6-9]\d{9}$/', $normalizedContact)) {
    respond(false, 'Please enter a correct number.', 422);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Please enter a valid email address.', 422);
}

$labels = [
    'site_visit' => 'Site Visit',
    'brochure' => 'Brochure Download',
    'floor_plan' => 'Floor Plan Download',
    'contact' => 'Contact Form',
];

$subject = 'New Site Visit Lead';
$leadLabel = $labels[$leadType] ?? $labels['site_visit'];

$body = '
  <h2>New Lead - ' . htmlspecialchars($leadLabel, ENT_QUOTES, 'UTF-8') . '</h2>
  <table cellpadding="8" cellspacing="0" border="0">
    <tr><td><strong>Name</strong></td><td>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td><strong>Contact Number</strong></td><td>' . htmlspecialchars($contact, ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td><strong>Email</strong></td><td>' . htmlspecialchars($email !== '' ? $email : 'Not provided', ENT_QUOTES, 'UTF-8') . '</td></tr>
    <tr><td><strong>Lead Type</strong></td><td>' . htmlspecialchars($leadLabel, ENT_QUOTES, 'UTF-8') . '</td></tr>
  </table>
';

$plainBody = "New Lead - {$leadLabel}\n"
    . "Name: {$name}\n"
    . "Contact Number: {$contact}\n"
    . 'Email: ' . ($email !== '' ? $email : 'Not provided') . "\n"
    . "Lead Type: {$leadLabel}\n";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = $config['smtp_host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['smtp_user'];
    $mail->Password = $config['smtp_pass'];
    $mail->SMTPSecure = $config['smtp_secure'];
    $mail->Port = $config['smtp_port'];

    $mail->setFrom($config['smtp_user'], 'Bhutani Cyberthum Website');
    $mail->addAddress($config['mail_to'], $config['mail_to_name']);

    if ($email !== '') {
        $mail->addReplyTo($email, $name);
    }

    $mail->isHTML(true);
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->AltBody = $plainBody;

    $mail->send();
    respond(true, 'Thank you. We will contact you soon.');
} catch (Exception $exception) {
    respond(false, 'Unable to send your details right now. Please try again later.', 500);
}
