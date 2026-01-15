<?php
require_once __DIR__ . '/../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your_email_adress';
    $mail->Password = 'your_app_password'; // App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('your_email_address', 'Task Manager');
    $mail->addAddress('your.other.email@gmail.com', 'Recipient');

    $mail->isHTML(true);
    $mail->Subject = 'Test Email';
    $mail->Body = 'This is a test email from local PHP using PHPMailer.';

    $mail->send();
    echo "Email sent successfully.";
} catch (Exception $e) {
    echo " Email failed: " . $mail->ErrorInfo;
}
