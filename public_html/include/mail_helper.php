<?php
/**
 * Shared helpers for the enquiry (mail.php) and newsletter (mail1.php) endpoints.
 *
 * SMTP credentials are NOT stored in this repository. They are read from
 * hgt-config.php one level above public_html (e.g. /home/<user>/hgt-config.php),
 * or from environment variables as a fallback. See config/hgt-config.example.php.
 */

if (!defined('HGT_MAIL_HELPER')) {
    define('HGT_MAIL_HELPER', true);

    require_once __DIR__ . '/../PHPMailer/PHPMailerAutoload.php';

    function hgt_config()
    {
        static $config = null;
        if ($config !== null) {
            return $config;
        }

        $config = array(
            'smtp_host'     => getenv('HGT_SMTP_HOST') ?: 'smtp.hostinger.com',
            'smtp_port'     => (int) (getenv('HGT_SMTP_PORT') ?: 587),
            'smtp_secure'   => getenv('HGT_SMTP_SECURE') ?: 'tls'   // STARTTLS on 587,
            'smtp_username' => getenv('HGT_SMTP_USERNAME') ?: 'info@holidaygurutravel.in',
            'smtp_password' => getenv('HGT_SMTP_PASSWORD') ?: '',
            'mail_from'     => getenv('HGT_MAIL_FROM') ?: 'info@holidaygurutravel.in',
            'mail_to'       => getenv('HGT_MAIL_TO') ?: 'info@holidaygurutravel.in',
        );

        $file = dirname(dirname(__DIR__)) . '/hgt-config.php';
        if (is_readable($file)) {
            $fromFile = include $file;
            if (is_array($fromFile)) {
                $config = array_merge($config, $fromFile);
            }
        }

        return $config;
    }

    /** Stop with the "0" response the front-end script expects on failure. */
    function hgt_fail($httpCode = 400)
    {
        http_response_code($httpCode);
        echo 0;
        exit;
    }

    /** Read a POST field as a single trimmed line, length-limited. */
    function hgt_field($name, $maxLength = 200)
    {
        $value = isset($_POST[$name]) && is_string($_POST[$name]) ? $_POST[$name] : '';
        $value = trim(preg_replace('/[\r\n\t]+/', ' ', $value));
        return mb_substr($value, 0, $maxLength, 'UTF-8');
    }

    /** Read a POST field that may span several lines (message box). */
    function hgt_text($name, $maxLength = 2000)
    {
        $value = isset($_POST[$name]) && is_string($_POST[$name]) ? $_POST[$name] : '';
        return mb_substr(trim($value), 0, $maxLength, 'UTF-8');
    }

    function hgt_e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Reject non-POST requests, bots that fill the hidden honeypot, submissions
     * made too quickly after page load, and more than 5 submissions per IP in
     * 10 minutes.
     */
    function hgt_guard_request()
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            hgt_fail(405);
        }

        // Honeypot: real visitors never see or fill this field.
        if (hgt_field('website') !== '') {
            hgt_fail(400);
        }

        // Timestamp added by the footer script when the page loads (ms).
        $loadedAt = (int) hgt_field('_ts', 20);
        if ($loadedAt <= 0 || (microtime(true) * 1000) - $loadedAt < 3000) {
            hgt_fail(400);
        }

        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown';
        $file = sys_get_temp_dir() . '/hgt_rl_' . md5($ip);
        $window = 600;
        $limit = 5;
        $now = time();
        $hits = array();
        if (is_readable($file)) {
            $hits = json_decode((string) @file_get_contents($file), true);
            $hits = is_array($hits) ? $hits : array();
        }
        $hits = array_values(array_filter($hits, function ($t) use ($now, $window) {
            return is_int($t) && $t > $now - $window;
        }));
        if (count($hits) >= $limit) {
            hgt_fail(429);
        }
        $hits[] = $now;
        @file_put_contents($file, json_encode($hits), LOCK_EX);
    }

    /** Page the form was submitted from, restricted to this site. */
    function hgt_source_page()
    {
        $page = hgt_field('page', 500);
        if ($page === '' && isset($_SERVER['HTTP_REFERER'])) {
            $page = mb_substr($_SERVER['HTTP_REFERER'], 0, 500, 'UTF-8');
        }
        return preg_match('#^https?://(www\.)?holidaygurutravel\.in/#i', $page) ? $page : '';
    }

    /**
     * Send an HTML email to the sales mailbox. $rows is label => plain value;
     * every value is escaped here. Returns true on success.
     */
    function hgt_send_mail($subject, array $rows, $replyTo = '', $replyName = '')
    {
        $config = hgt_config();
        if ($config['smtp_password'] === '') {
            // Test servers only: HGT_MAIL_DUMP (a file path set in the server environment) records
            // what would have been mailed, so enquiry content can be verified without SMTP.
            $dump = getenv('HGT_MAIL_DUMP');
            if ($dump) @file_put_contents($dump, json_encode(array('subject' => $subject, 'rows' => $rows)) . "\n", FILE_APPEND);
            error_log('hgt mail: SMTP password not configured (hgt-config.php missing?)');
            return false;
        }

        $body = '<html><body><table cellpadding="6" style="font-family:Arial,sans-serif;font-size:14px">';
        foreach ($rows as $label => $value) {
            $body .= '<tr><td style="color:#5B6478"><strong>' . hgt_e($label) . '</strong></td><td>'
                . nl2br(hgt_e($value)) . '</td></tr>';
        }
        $body .= '</table></body></html>';

        $mail = new PHPMailer();
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->SMTPDebug = 0;
        $mail->SMTPAuth = true;
        $mail->Host = $config['smtp_host'];
        $mail->Port = $config['smtp_port'];
        $mail->SMTPSecure = $config['smtp_secure'];
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];

        $mail->setFrom($config['mail_from'], 'Holiday Guru Travel');
        $mail->addAddress($config['mail_to']);
        if ($replyTo !== '' && PHPMailer::validateAddress($replyTo)) {
            $mail->addReplyTo($replyTo, $replyName);
        }

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = implode("\n", array_map(function ($label, $value) {
            return $label . ': ' . $value;
        }, array_keys($rows), $rows));

        if (!$mail->send()) {
            error_log('hgt mail: send failed: ' . $mail->ErrorInfo);
            return false;
        }
        return true;
    }
}
