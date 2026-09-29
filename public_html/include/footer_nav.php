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
 *                page exists, otherwise "Coming soon" (a missing page never
 *                becomes a broken link). 'coming_soon': force Coming soon.
 *                'hidden': not shown (visibility).
 *   order        Optional number; lower shows first. Default: file order.
 *   badge        Optional short tag shown after a live link, e.g. 'New'.
 *   target       Optional, e.g. '_blank' for external URLs.
 *   description  Optional note for editors (not shown on the site).
 *
 * Section = the array the item sits in. Move an item to change its section.
 *
 * Rule: never point an item at a page that does not match its label.
 * A "Coming soon" item is plain text (no href), so search engines see no link.
 */

return array(
    'domestic' => array(
        'title' => 'Domestic',
        'items' => array(
            array('label' => 'Domestic Tour Packages', 'url' => '/domestic-holidays'),
            array('label' => 'Kashmir', 'url' => '/tours/kashmir', 'description' => 'Kashmir destination page: lists the Srinagar, Gulmarg, Pahalgam and Katra packages.'),
            array('label' => 'Kerala', 'url' => '/exotic-kerala', 'description' => 'Kerala hub: lists the Munnar, Thekkady, Alleppey and Kovalam packages.'),
            array('label' => 'Goa', 'url' => '/amazing-goa', 'description' => 'Goa hub: lists the Goa packages.'),
            array('label' => 'Popular Indian Destinations', 'url' => '',
                'description' => '/destinations lists worldwide destinations, not Indian ones only.'),
            array('label' => 'Family Holidays', 'url' => '',
                'description' => '/family-holiday is a 98% copy of the other theme pages (same Goa/Dubai/Mussoorie packages). Activate after it has genuine family content.'),
            array('label' => 'Honeymoon Holidays', 'url' => '',
                'description' => '/honeymoon-holiday is a 99% copy of /family-holiday. Activate after rewrite.'),
            array('label' => 'Pilgrimage Tours', 'url' => '/religious-tour',
                'description' => 'Links the Amarnath Yatra and Char Dham hubs. (/pilgrim-holidays shows Goa and Dubai packages, so it is not used.)'),
            array('label' => 'Adventure Tours', 'url' => '',
                'description' => '/adventure-holiday is a 98% copy of /family-holiday. Activate after rewrite.'),
            array('label' => 'Weekend Getaways', 'url' => ''),
        ),
    ),
    'international' => array(
        'title' => 'International',
        'items' => array(
            array('label' => 'International Tour Packages', 'url' => '/international-holidays'),
            array('label' => 'Popular Countries', 'url' => ''),
            array('label' => 'Europe', 'url' => '', 'description' => 'No Europe packages in the inventory.'),
            array('label' => 'Dubai', 'url' => '/dream-dubai', 'description' => 'Lists the Dubai packages.'),
            array('label' => 'Singapore', 'url' => '/sizzling-singapore', 'description' => 'Singapore hub: lists the Singapore and Malaysia packages.'),
            array('label' => 'Southeast Asia', 'url' => '', 'description' => 'Needs a hub for the Singapore, Malaysia and Thailand packages.'),
            array('label' => 'Maldives', 'url' => '/maldives-05-days', 'description' => 'Only Maldives package; replace with a hub when more exist.'),
            array('label' => 'Thailand', 'url' => '',
                'description' => '/thriller-thailand has only a heading and breadcrumb (no content, no packages). Set url to /thriller-thailand once it has real content.'),
            array('label' => 'International Honeymoon', 'url' => ''),
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
        'items' => array(
            array('label' => 'About Holiday Guru Travel', 'url' => '/about'),
            array('label' => 'Contact Us', 'url' => '/contact'),
            array('label' => 'Why Holiday Guru', 'url' => ''),
            array('label' => 'Our Team', 'url' => ''),
            array('label' => 'Travel Experts', 'url' => ''),
            array('label' => 'Careers', 'url' => ''),
            array('label' => 'Blog / Travel Inspiration', 'url' => '',
                'description' => 'blog.html / blog-details.html are template demo pages; do not link them.'),
        ),
    ),
    'legal' => array(
        'title' => 'Legal & Support',
        'items' => array(
            array('label' => 'FAQs', 'url' => '/faqs'),
            array('label' => 'Contact Support', 'url' => '/contact'),
            array('label' => 'Privacy Policy', 'url' => ''),
            array('label' => 'Cookie Policy', 'url' => ''),
            array('label' => 'Terms & Conditions', 'url' => ''),
            array('label' => 'Cancellation & Refund Policy', 'url' => ''),
            array('label' => 'Disclaimer', 'url' => ''),
            array('label' => 'Sitemap', 'url' => '', 'description' => 'Planned as an HTML sitemap page; /sitemap.xml is for search engines.'),
        ),
    ),
);
