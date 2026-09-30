<?php
/** Leadership / Our Team pages. Profiles: include/content/people.php. Status: include/data/page-status.php. */
require_once __DIR__ . '/../ui/core.php';

if (!function_exists('hg_render_people')) {
    function hg_person_card(array $p)
    {
        $name = trim($p['name']) !== '' ? hg_e($p['name']) : '<span class="hg-muted">Name to be added</span>';
        $role = trim($p['role']) !== '' ? $p['role'] : 'Role to be added';
        return '<article class="hg-person">' . hg_img($p['photo'], trim($p['name']) !== '' ? $p['name'] : '', 400, 480, 'hg-person__photo')
            . '<div class="hg-person__body"><h3 class="hg-person__name">' . $name . '</h3><p class="hg-person__role">' . hg_e($role) . '</p>'
            . (trim($p['bio']) !== '' ? '<p class="hg-person__bio">' . hg_e($p['bio']) . '</p>' : '') . '</div></article>';
    }

    function hg_render_people($which)
    {
        $people = include dirname(__DIR__) . '/content/people.php';
        $leader = $which === 'leadership';
        $path = $leader ? '/leadership' : '/our-team';
        $h1 = $leader ? 'Leadership' : 'Our team';
        hg_layout_start(array(
            'title' => $h1 . ' | Holiday Guru Travel',
            'description' => $leader ? 'The leadership of Holiday Guru Travel (' . HG_LEGAL_NAME . '), Noida.' : 'Meet the Holiday Guru Travel team who plan and support your holidays from our office in Noida.',
            'path' => $path, 'index' => hg_page_status($path) === 'approved',
            'breadcrumbs' => array(array('Home', '/'), array('About Us', '/about'), array($h1, null)),
        ));
        ?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow">Company</p>
        <h1 class="hg-h1" id="page-title"><?= hg_e($h1) ?></h1>
        <p class="hg-lead"><?= $leader
            ? 'Holiday Guru Travel is operated by ' . hg_e(HG_LEGAL_NAME) . ', with its office in Sector 27, Noida.'
            : 'The travel experts who plan your itinerary, confirm your hotels and stay in touch on WhatsApp through your trip — from our office in Sector 27, Noida.' ?></p>
    </div>
</section>
<section class="hg-section hg-section--tight" aria-label="<?= hg_e($h1) ?>">
    <div class="hg-container">
        <div class="hg-people<?= $leader ? ' hg-people--lead' : '' ?>"><?php foreach ($people[$which] as $p) echo hg_person_card($p); ?></div>
    </div>
</section>
<?php if (!$leader) { ?>
<section class="hg-section hg-section--tint" aria-labelledby="how-title">
    <div class="hg-container">
        <?= hg_section_head('How we work for you', 'From enquiry to your return', '', null, 'how-title') ?>
        <ol class="hg-steps hg-steps--light">
            <li><strong>Your enquiry</strong><span>A travel expert calls or WhatsApps you to understand your dates, travellers and budget.</span></li>
            <li><strong>Your itinerary</strong><span>We send a day-by-day plan with hotels, inclusions, exclusions and a quote.</span></li>
            <li><strong>Your booking</strong><span>A 35% advance confirms the trip; we issue your booking voucher once payment is received.</span></li>
            <li><strong>On the trip</strong><span>Reach us on WhatsApp 24×7 at <?= hg_e(HG_PHONE_DISPLAY) ?>.</span></li>
        </ol>
    </div>
</section>
<?php } ?>
<?= hg_cta_band('Talk to our team', 'Tell us where you want to go — a travel expert will call or WhatsApp you.') ?>
<?php
        hg_layout_end();
    }
}
