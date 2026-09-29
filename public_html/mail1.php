<?php
// Newsletter subscribe endpoint. Responds "1" on success, "0" on failure.
require __DIR__ . '/include/mail_helper.php';

hgt_guard_request();

$email = hgt_field('email', 150);
if (!PHPMailer::validateAddress($email)) {
    hgt_fail(422);
}

$sent = hgt_send_mail('Newsletter subscription', array(
    'Email' => $email,
    'Page'  => hgt_source_page(),
), $email);

echo $sent ? 1 : 0;
