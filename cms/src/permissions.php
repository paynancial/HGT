<?php
/**
 * Roles and permissions (from the blueprint's "Roles & Permissions" sheet, plus the brief's
 * Package Manager and Reviewer roles).
 *
 * Levels, lowest to highest: none < view < request < review < edit < manage < full.
 *   view     read only
 *   request  may draft a change (e.g. a draft price version) but not approve it
 *   review   may sign off a review stage
 *   edit     may change content
 *   manage   may change and approve within the module
 *   full     everything, including destructive actions
 */

const HG_LEVELS = array('none' => 0, 'view' => 1, 'request' => 2, 'review' => 3, 'edit' => 4, 'manage' => 5, 'full' => 6);

const HG_ROLES = array(
    'super_admin'       => 'Super Admin',
    'admin'             => 'Admin',
    'package_manager'   => 'Package Manager',
    'content_manager'   => 'Content Manager',
    'seo_manager'       => 'SEO Manager',
    'pricing_manager'   => 'Pricing Manager',
    'sales_manager'     => 'Sales Manager',
    'travel_consultant' => 'Travel Consultant',
    'reviewer'          => 'Reviewer',
    'finance'           => 'Finance',
    'marketing'         => 'Marketing',
);

// module => role => level
const HG_MATRIX = array(
    'packages'   => array('super_admin' => 'full', 'admin' => 'full', 'package_manager' => 'manage', 'content_manager' => 'edit', 'seo_manager' => 'view', 'pricing_manager' => 'view', 'sales_manager' => 'view', 'travel_consultant' => 'edit', 'reviewer' => 'review', 'finance' => 'view', 'marketing' => 'view'),
    'itinerary'  => array('super_admin' => 'full', 'admin' => 'full', 'package_manager' => 'manage', 'content_manager' => 'edit', 'seo_manager' => 'view', 'pricing_manager' => 'view', 'sales_manager' => 'view', 'travel_consultant' => 'edit', 'reviewer' => 'review', 'finance' => 'view', 'marketing' => 'view'),
    'pricing'    => array('super_admin' => 'full', 'admin' => 'manage', 'package_manager' => 'view', 'content_manager' => 'view', 'seo_manager' => 'view', 'pricing_manager' => 'manage', 'sales_manager' => 'view', 'travel_consultant' => 'request', 'reviewer' => 'review', 'finance' => 'manage', 'marketing' => 'view'),
    'media'      => array('super_admin' => 'full', 'admin' => 'manage', 'package_manager' => 'manage', 'content_manager' => 'manage', 'seo_manager' => 'view', 'pricing_manager' => 'view', 'sales_manager' => 'view', 'travel_consultant' => 'view', 'reviewer' => 'view', 'finance' => 'view', 'marketing' => 'manage'),
    'scope'      => array('super_admin' => 'full', 'admin' => 'manage', 'package_manager' => 'manage', 'content_manager' => 'manage', 'seo_manager' => 'review', 'pricing_manager' => 'view', 'sales_manager' => 'view', 'travel_consultant' => 'view', 'reviewer' => 'review', 'finance' => 'view', 'marketing' => 'view'),
    'addons'     => array('super_admin' => 'full', 'admin' => 'manage', 'package_manager' => 'view', 'content_manager' => 'view', 'seo_manager' => 'view', 'pricing_manager' => 'manage', 'sales_manager' => 'view', 'travel_consultant' => 'request', 'reviewer' => 'view', 'finance' => 'view', 'marketing' => 'view'),
    'offers'     => array('super_admin' => 'full', 'admin' => 'manage', 'package_manager' => 'view', 'content_manager' => 'view', 'seo_manager' => 'view', 'pricing_manager' => 'manage', 'sales_manager' => 'view', 'travel_consultant' => 'view', 'reviewer' => 'view', 'finance' => 'view', 'marketing' => 'manage'),
    'seo'        => array('super_admin' => 'full', 'admin' => 'view', 'package_manager' => 'view', 'content_manager' => 'edit', 'seo_manager' => 'manage', 'pricing_manager' => 'edit', 'sales_manager' => 'view', 'travel_consultant' => 'view', 'reviewer' => 'review', 'finance' => 'view', 'marketing' => 'view'),
    'curation'   => array('super_admin' => 'full', 'admin' => 'manage', 'package_manager' => 'manage', 'content_manager' => 'view', 'seo_manager' => 'view', 'pricing_manager' => 'view', 'sales_manager' => 'view', 'travel_consultant' => 'none', 'reviewer' => 'view', 'finance' => 'none', 'marketing' => 'manage'),
    'enquiries'  => array('super_admin' => 'full', 'admin' => 'manage', 'package_manager' => 'view', 'content_manager' => 'view', 'seo_manager' => 'view', 'pricing_manager' => 'view', 'sales_manager' => 'manage', 'travel_consultant' => 'manage', 'reviewer' => 'none', 'finance' => 'view', 'marketing' => 'view'),
    'users'      => array('super_admin' => 'full', 'admin' => 'manage'),
    'activity'   => array('super_admin' => 'full', 'admin' => 'view', 'package_manager' => 'view', 'content_manager' => 'view', 'seo_manager' => 'view', 'pricing_manager' => 'view', 'sales_manager' => 'view', 'travel_consultant' => 'view', 'reviewer' => 'view', 'finance' => 'view', 'marketing' => 'view'),
);

// Workflow actions and who may do them (separate from module levels on purpose).
const HG_ACTIONS = array(
    'submit_review'     => array('super_admin', 'admin', 'package_manager', 'content_manager', 'travel_consultant'),
    'review_content'    => array('super_admin', 'admin', 'package_manager', 'content_manager', 'reviewer'),
    'review_seo'        => array('super_admin', 'admin', 'seo_manager', 'reviewer'),
    'review_pricing'    => array('super_admin', 'admin', 'pricing_manager', 'finance', 'reviewer'),
    'approve'           => array('super_admin', 'admin'),
    'publish'           => array('super_admin', 'admin'),
    'pause'             => array('super_admin', 'admin', 'package_manager'),
    'archive'           => array('super_admin', 'admin'),
    'restore'           => array('super_admin', 'admin'),
    'delete'            => array('super_admin'),
    'approve_rate'      => array('super_admin', 'admin', 'pricing_manager', 'finance'),
    'assign_package_id' => array('super_admin'),
    'publish_offer'     => array('super_admin', 'admin', 'pricing_manager', 'marketing'),
    'override_duration' => array('super_admin', 'admin', 'package_manager'),
);

function hg_level($role, $module)
{
    $l = isset(HG_MATRIX[$module][$role]) ? HG_MATRIX[$module][$role] : 'none';
    return HG_LEVELS[$l];
}

/** Can $role act on $module at $need level or above? */
function hg_can($role, $module, $need = 'view')
{
    return hg_level($role, $module) >= HG_LEVELS[$need];
}

function hg_may($role, $action)
{
    return isset(HG_ACTIONS[$action]) && in_array($role, HG_ACTIONS[$action], true);
}
