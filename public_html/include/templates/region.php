<?php
/**
 * Region listing (/domestic-holidays, /international-holidays).
 * Destinations and packages come from include/data (real package pages only).
 */
require_once __DIR__ . '/../ui/core.php';

if (!function_exists('hg_render_region')) {
    function hg_render_region($region, array $meta)
    {
        $groups = array_values(array_filter(hg_groups(), function ($g) use ($region) { return $g['region'] === $region && $g['count'] > 0; }));
        $isIndia = $region === 'india';
        $faqs = hg_booking_faqs();
        $items = array();
        $pos = 0;
        foreach ($groups as $g) $items[] = array('@type' => 'ListItem', 'position' => ++$pos, 'name' => $g['name'] . ' tour packages', 'url' => hg_abs($g['hub_url']));

        hg_layout_start(array(
            'title' => $meta['title'], 'description' => $meta['description'], 'path' => $meta['path'],
            'breadcrumbs' => array(array('Home', '/'), array($meta['h1'], null)),
            'schema' => array(
                array('@type' => 'CollectionPage', 'name' => $meta['h1'], 'url' => hg_abs($meta['path']), 'about' => $meta['description']),
                array('@type' => 'ItemList', 'name' => $meta['h1'], 'itemListElement' => $items),
                hg_faq_schema($faqs),
            ),
        ));
        $nav = array();
        foreach ($groups as $g) $nav[] = array('d-' . $g['key'], $g['name']);
        $nav[] = array('faq', 'FAQs');
        ?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container">
        <p class="hg-eyebrow"><?= $isIndia ? 'India' : 'International' ?></p>
        <h1 class="hg-h1" id="page-title"><?= hg_e($meta['h1']) ?></h1>
        <p class="hg-lead"><?= hg_e($meta['lead']) ?> Every tour comes with a day-by-day plan, inclusions and exclusions.</p>
        <div class="hg-pagehead__actions">
            <a class="hg-btn hg-btn--primary" href="/tours">Search all packages</a>
            <a class="hg-btn hg-btn--outline" href="/customized-holidays">Plan a customized holiday</a>
        </div>
    </div>
</section>

<?= hg_section_nav($nav, 'Destinations on this page') ?>

<section class="hg-section hg-section--tight" aria-labelledby="dest-title">
    <div class="hg-container">
        <h2 class="hg-h2" id="dest-title">Choose a destination</h2>
        <div class="hg-grid hg-grid--dest"><?php foreach ($groups as $g) echo hg_destination_card($g); ?></div>
        <?php if (!empty($meta['soon'])) { ?>
        <p class="hg-muted" style="margin-top:20px">Also planned on request: <?= hg_e(implode(', ', $meta['soon'])) ?>. <a href="/customized-holidays">Ask us</a> and we will plan the trip for you.</p>
        <?php } ?>
    </div>
</section>

<?php foreach ($groups as $i => $g) { $pk = hg_packages_in($g['key']); usort($pk, function ($a, $b) { return $a['days'] <=> $b['days']; }); ?>
<section class="hg-section hg-section--tight<?= $i % 2 ? '' : ' hg-section--tint' ?>" id="d-<?= hg_e($g['key']) ?>" aria-labelledby="t-<?= hg_e($g['key']) ?>" style="scroll-margin-top:calc(var(--hg-sticky) + 56px)">
    <div class="hg-container">
        <?= hg_section_head($g['area'], $g['name'] . ' tour packages', hg_e(hg_duration_range($pk)) . ' trips', array('Explore ' . $g['name'] . ' tours', $g['hub_url']), 't-' . $g['key']) ?>
        <?= hg_package_grid(array_slice($pk, 0, 4)) ?>
        <?php if (count($pk) > 4) { ?><p style="margin-top:20px"><a class="hg-link-arrow" href="/tours/<?= hg_e($g['key']) ?>">Compare all <?= hg_e($g['name']) ?> tours with filters <span aria-hidden="true">&rarr;</span></a></p><?php } ?>
    </div>
</section>
<?php } ?>

<section class="hg-section" id="faq" aria-labelledby="faq-title">
    <div class="hg-container hg-narrow">
        <h2 class="hg-h2" id="faq-title">Booking questions</h2>
        <?= hg_faq($faqs) ?>
    </div>
</section>

<?= hg_cta_band('Not sure where to go?', 'Tell us your dates, budget and who is travelling — a travel expert suggests options and a day-by-day plan.') ?>
<?php
        hg_layout_end();
    }
}
