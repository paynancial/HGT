<?php
/**
 * Footer navigation (owner-approved information architecture, 2026-09-29).
 *
 * Edit this file to change labels, order, URLs or visibility; the footer
 * template never needs to change.
 *
 * Item keys:
 *   label        Text shown to visitors (required)
 *   url          Real published URL. Empty = the page does not exist yet.
 *   status       'auto' (default) or 'active': link when url is set and the
 *                page exists, otherwise plain text with no link (a missing page
 *                never becomes a broken link). 'coming_soon': force plain text.
 *                'hidden': not shown (visibility).
 *   order        Optional number; lower shows first. Default: file order.
 *   badge        Optional short tag shown after a live link, e.g. 'New'.
 *   target       Optional, e.g. '_blank' for external URLs.
 *   description  Optional note for editors (not shown on the site).
 *
 * Section = the array the item sits in. Move an item to change its section.
 *
 * Rule: never point an item at a page that does not match its label.
 * An item without a live page is plain text (no href, no badge), so search engines see no link.
 */

return array(
    'domestic' => array(
        'title' => 'Domestic',
        // Owner rule (2026-09-30): at most 6-7 state/destination names in this column.
        'items' => array(
            array('label' => 'Kashmir', 'url' => '/tours/kashmir'),
            array('label' => 'Himachal Pradesh', 'url' => '/tours/himachal'),
            array('label' => 'Uttarakhand', 'url' => '/tours/uttarakhand'),
            array('label' => 'Kerala', 'url' => '/tours/kerala'),
            array('label' => 'Goa', 'url' => '/tours/goa'),
            array('label' => 'Leh Ladakh', 'url' => '/tours/ladakh'),
            array('label' => 'Sikkim & Darjeeling', 'url' => '/tours/sikkim-darjeeling'),
            array('label' => 'All domestic tours', 'url' => '/domestic-holidays'),
        ),
    ),
    'international' => array(
        'title' => 'International',
        // Owner rule (2026-09-30): at most 6-7 country names in this column.
        'items' => array(
            array('label' => 'Dubai (UAE)', 'url' => '/tours/dubai'),
            array('label' => 'Singapore', 'url' => '/tours/singapore-malaysia'),
            array('label' => 'Malaysia', 'url' => '/tours/singapore-malaysia', 'description' => 'Singapore & Malaysia destination page (Kuala Lumpur itineraries).'),
            array('label' => 'Thailand', 'url' => '/tours/singapore-malaysia', 'description' => 'Thailand is covered by the 9-day Singapore–Malaysia–Thailand itineraries on this page.'),
            array('label' => 'Maldives', 'url' => '/tours/maldives'),
            array('label' => 'Europe', 'url' => '', 'description' => 'No Europe packages yet.'),
            array('label' => 'All international tours', 'url' => '/international-holidays'),
        ),
    ),
    'inbound' => array(
        'title' => 'Inbound',
        'items' => array(
            // Inbound pages are written for travellers visiting India from abroad.
            // Do not point these at the domestic pages.
            array('label' => 'India Tours for Foreign Travellers', 'url' => '/india-tours'),
            array('label' => 'Rajasthan', 'url' => ''),
            array('label' => 'Kerala', 'url' => ''),
            array('label' => 'Kashmir', 'url' => ''),
            array('label' => 'Golden Triangle', 'url' => ''),
            array('label' => 'Cultural Tours', 'url' => ''),
            array('label' => 'Customized India Tours', 'url' => ''),
        ),
    ),
    'company' => array(
        'title' => 'Company',
        // Owner-specified order (2026-09-30). Pages without content show as plain text (no link).
        'items' => array(
            array('label' => 'About Us', 'url' => '/about'),
            array('label' => 'Why Us?', 'url' => '/about#why-us', 'description' => 'The "Why choose us" section of the About page.'),
            array('label' => 'Leadership', 'url' => '/leadership', 'description' => 'Shows as plain text (no link) until approved in page-status.php.'),
            array('label' => 'Our Team', 'url' => '/our-team', 'description' => 'Shows as plain text (no link) until approved in page-status.php.'),
            array('label' => 'Blog', 'url' => '', 'description' => 'No articles yet (blog.html was a template demo and now returns 404).'),
            array('label' => 'Career', 'url' => '', 'description' => 'Needs current openings from the owner.'),
        ),
    ),
    'legal' => array(
        'title' => 'Legal & Support',
        // Owner-specified order (2026-09-30). Pages awaiting approval show as plain text (no link).
        'items' => array(
            array('label' => 'Cancellation Policy', 'url' => '/cancellation-policy'),
            array('label' => 'Refund Policy', 'url' => '/refund-policy'),
            array('label' => 'Payment Policy', 'url' => '/payment-policy'),
            array('label' => 'Payment Link', 'url' => '', 'description' => 'Set to the payment page or gateway link once online payment is configured.'),
            array('label' => 'Grievance Redress', 'url' => '/grievance-redress'),
            array('label' => 'Disclaimer', 'url' => '', 'description' => 'Needs disclaimer text (legal).'),
            array('label' => 'Contact Us', 'url' => '/contact'),
        ),
    ),
);
