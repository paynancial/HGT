<?php
/**
 * Publishing status of pages awaiting owner approval (one place to approve).
 *   'draft'    — visible on the site but hidden from Google (noindex, not in sitemap.xml)
 *   'approved' — indexable; add the URL to sitemap.xml
 * Change a value to 'approved' once the owner has approved the page content.
 */
return array(
    '/faqs'                 => 'draft',
    '/india-tours'          => 'draft',
    '/travel-guide/kashmir' => 'draft',
    '/cancellation-policy'  => 'draft',
    '/refund-policy'        => 'draft',
    '/payment-policy'       => 'draft',
    '/leadership'           => 'draft',   // photos, names and roles to be supplied by the owner
    '/our-team'             => 'draft',   // photos, names and roles to be supplied by the owner
);
