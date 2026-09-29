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

$rows = array(
    'Name'               => $name,
    'Email'              => $email,
    'Phone'              => $phone,
    'No. of travellers'  => $travellers,
);

// Optional trip details sent by the Phase 1 enquiry forms (all plain text, escaped when mailed).
$optional = array(
    'enquiry_type'   => 'Enquiry type',
    'package'        => 'Package',
    'destination'    => 'Destination',
    'travel_date'    => 'Travel date',
    'travel_month'   => 'Travel month',
    'adults'         => 'Adults',
    'children'       => 'Children',
    'departure_city' => 'Departure city',
    'budget'         => 'Budget per person',
    'holiday_type'   => 'Holiday type',
    'hotel_category' => 'Hotel category',
    'duration'       => 'Trip length',
    'country'        => 'Country of residence',
);
foreach ($optional as $key => $label) {
    $value = hgt_field($key, 150);
    if ($value !== '') {
        $rows[$label] = $value;
    }
}
$rows['Message'] = $message;
$rows['Page'] = $page;

$subject = 'Website enquiry: ' . $name;
if (isset($rows['Package'])) {
    $subject .= ' - ' . $rows['Package'];
} elseif (isset($rows['Destination'])) {
    $subject .= ' - ' . $rows['Destination'];
}

$sent = hgt_send_mail($subject, $rows, $email, $name);

echo $sent ? 1 : 0;
