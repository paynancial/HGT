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
    // Footer redesign (owner reference, 2026-09-30): Tours · Destinations · Company · Support (+ brand and Contact
    // columns in the template). Only real pages are linked; items without a page show as plain text.
    'tours' => array(
        'title' => 'Tours',
        'items' => array(
            array('label' => 'Domestic Tours', 'url' => '/domestic-holidays'),
            array('label' => 'International Tours', 'url' => '/international-holidays'),
            array('label' => 'Inbound Tours', 'url' => '/india-tours', 'description' => 'India tours for travellers visiting from abroad.'),
            array('label' => 'Pilgrimage Tours', 'url' => '/religious-tour'),
            array('label' => 'Customised Tours', 'url' => '/customized-holidays'),
            array('label' => 'Offers', 'url' => '/offers'),
            array('label' => 'Search Packages', 'url' => '/tours'),
        ),
    ),
    'destinations' => array(
        'title' => 'Destinations',
        'items' => array(
            array('label' => 'Kashmir', 'url' => '/tours/kashmir'),
            array('label' => 'Himachal Pradesh', 'url' => '/tours/himachal'),
            array('label' => 'Uttarakhand', 'url' => '/tours/uttarakhand'),
            array('label' => 'Kerala', 'url' => '/tours/kerala'),
            array('label' => 'Goa', 'url' => '/tours/goa'),
            array('label' => 'Dubai', 'url' => '/tours/dubai'),
            array('label' => 'Singapore & Malaysia', 'url' => '/tours/singapore-malaysia'),
            array('label' => 'Maldives', 'url' => '/tours/maldives'),
        ),
    ),
    'company' => array(
        'title' => 'Company',
        'items' => array(
            array('label' => 'About Us', 'url' => '/about'),
            array('label' => 'Why Choose Us', 'url' => '/about#why-us', 'description' => 'The "Why choose us" section of the About page.'),
            array('label' => 'Leadership', 'url' => '/leadership'),
            array('label' => 'Our Team', 'url' => '/our-team'),
            array('label' => 'Blog', 'url' => '', 'description' => 'No articles yet. Set a URL and remove status to show it.'),
            array('label' => 'Career', 'url' => '', 'description' => 'Needs current openings from the owner.'),
        ),
    ),
    'support' => array(
        'title' => 'Support',
        'items' => array(
            array('label' => 'FAQs', 'url' => '/faqs'),
            array('label' => 'Cancellation Policy', 'url' => '/cancellation-policy'),
            array('label' => 'Refund Policy', 'url' => '/refund-policy'),
            array('label' => 'Payment Policy', 'url' => '/payment-policy'),
            array('label' => 'Grievance Redress', 'url' => '/grievance-redress'),
            array('label' => 'Payment Link', 'url' => '', 'description' => 'Set to the payment page or gateway link once online payment is configured.'),
            array('label' => 'Disclaimer', 'url' => '', 'description' => 'Needs disclaimer text (legal).'),
        ),
    ),
);
