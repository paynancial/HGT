<?php
// Enquiry form endpoint (contactForm1/2/3). Responds "1" on success, "0" on failure.
require __DIR__ . '/include/mail_helper.php';

hgt_guard_request();

$name       = hgt_field('name', 100);
$email      = hgt_field('email', 150);
$phone      = preg_replace('/[^0-9+ ]/', '', hgt_field('phone', 20));
$travellers = hgt_field('travellers', 10);
$message    = hgt_text('message', 2000);
$page       = hgt_source_page();

if ($name === '' || $phone === '' || !PHPMailer::validateAddress($email)) {
    hgt_fail(422);
}

$sent = hgt_send_mail('Website enquiry: ' . $name, array(
    'Name'               => $name,
    'Email'              => $email,
    'Phone'              => $phone,
    'No. of travellers'  => $travellers,
    'Message'            => $message,
    'Page'               => $page,
), $email, $name);

echo $sent ? 1 : 0;
