<?php
return [
    'host' => 'smtp.example.com',
    'smtp_auth' => true,
    'username' => 'your_email@example.com',
    'password' => 'your_email_password',
    'smtp_secure' => PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
    'port' => 587,
];
?>