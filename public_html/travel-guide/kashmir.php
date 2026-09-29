<?php
// Kashmir travel guide (Article). Practical advice; the package list lives on
// /tours/kashmir. STATUS: draft — noindex until the owner approves.
require __DIR__ . '/../include/ui/core.php';

$c = include __DIR__ . '/../include/content/kashmir.php';
$status = 'draft';
$sample = hg_package('srinagar-gulmarg-pahalgam-tour-package-5-days');
$pack = array(
    array('March – May', 'Light woollens and a jacket; rain layer; comfortable walking shoes.'),
    array('June – August', 'Cotton clothes for the day, a fleece or jacket for evenings and for Gulmarg and Sonmarg; sunscreen and sunglasses.'),
    array('September – November', 'Layers — warm sweater and jacket for cool mornings and evenings.'),
    array('December – February', 'Heavy winter wear: thermal layers, down jacket, gloves, cap and waterproof boots for snow.'),
);
$toc = array(array('when', 'When to go'), array('reach', 'How to reach'), array('around', 'Getting around'), array('pack', 'What to pack'), array('plan', 'A 5-day plan'), array('tips', 'Practical tips'));

hg_layout_start(array(
    'title' => 'Kashmir Travel Guide: When to Go, How to Reach, What to Pack | Holiday Guru Travel',
    'description' => 'Plan a Kashmir trip: the best months for sightseeing or snow, how to reach Srinagar, getting to Gulmarg, Pahalgam and Sonmarg, what to pack by season and practical tips.',
    'path' => '/travel-guide/kashmir', 'index' => $status === 'approved', 'type' => 'article',
    'image' => $c['image'],
    'breadcrumbs' => array(array('Home', '/'), array('Kashmir', '/tours/kashmir'), array('Kashmir travel guide', null)),
    'schema' => array(array(
        '@type' => 'Article', 'headline' => 'Kashmir travel guide', 'description' => 'When to go, how to reach, getting around and what to pack for Kashmir.',
        'author' => array('@id' => HG_SITE_URL . '/#organization'), 'publisher' => array('@id' => HG_SITE_URL . '/#organization'),
        'dateModified' => $c['reviewed'], 'mainEntityOfPage' => hg_abs('/travel-guide/kashmir'), 'image' => hg_abs($c['image']),
    )),
));
?>
<article class="hg-guidepage">
<header class="hg-pagehead">
    <div class="hg-container hg-narrow">
        <p class="hg-eyebrow">Travel guide · Kashmir</p>
        <h1 class="hg-h1">Kashmir travel guide</h1>
        <p class="hg-lead">When to go, how to reach Srinagar, getting around the valley and what to pack — practical advice from the team that plans our Kashmir trips.</p>
        <p class="hg-muted" style="font-size:14px">By Holiday Guru Travel · Updated <?= hg_e(hg_date_label($c['reviewed'])) ?></p>
    </div>
</header>

<div class="hg-container hg-narrow">
    <nav class="hg-toc" aria-label="In this guide"><p>In this guide</p><ol><?php foreach ($toc as $t) { ?><li><a href="#<?= $t[0] ?>"><?= hg_e($t[1]) ?></a></li><?php } ?></ol></nav>

    <section class="hg-answer" id="when" aria-labelledby="g-when">
        <h2 class="hg-h2" id="g-when">When to go</h2>
        <p class="hg-answer__direct"><?= hg_e($c['best_time_answer']) ?></p>
        <div class="hg-tablewrap" tabindex="0" role="region" aria-label="Table"><table class="hg-table"><thead><tr><th scope="col">Months</th><th scope="col">Season</th><th scope="col">What to expect</th></tr></thead><tbody>
        <?php foreach ($c['best_time'] as $r) { ?><tr><td><?= hg_e($r[0]) ?></td><td><?= hg_e($r[1]) ?></td><td><?= hg_e($r[2]) ?></td></tr><?php } ?>
        </tbody></table></div>
    </section>

    <section class="hg-answer" id="reach" aria-labelledby="g-reach">
        <h2 class="hg-h2" id="g-reach">How to reach Kashmir</h2>
        <p class="hg-answer__direct">Fly to Srinagar airport (SXR), which has direct flights from Delhi and other major Indian cities; most itineraries start there.</p>
        <div class="hg-prose"><p><?= hg_e($c['transport']) ?></p><p>If you are combining Kashmir with Mata Vaishno Devi, the longer itineraries continue by road from Srinagar to Katra (about 268 km, 6–7 hours) and end in Jammu.</p></div>
    </section>

    <section class="hg-answer" id="around" aria-labelledby="g-around">
        <h2 class="hg-h2" id="g-around">Getting around</h2>
        <p class="hg-answer__direct">Srinagar is the base; Gulmarg, Pahalgam and Sonmarg are day trips or short stays by road.</p>
        <div class="hg-places"><?php foreach (array_slice($c['places'], 0, 4) as $pl) { ?><div class="hg-place"><h3 class="hg-h3" style="font-size:18px"><?= hg_e($pl[0]) ?></h3><p><?= hg_e($pl[1]) ?></p></div><?php } ?></div>
    </section>

    <section class="hg-answer" id="pack" aria-labelledby="g-pack">
        <h2 class="hg-h2" id="g-pack">What to pack</h2>
        <p class="hg-answer__direct">Pack layers in every season — evenings are cool even in summer, and Gulmarg and Sonmarg are colder than Srinagar.</p>
        <div class="hg-tablewrap" tabindex="0" role="region" aria-label="Table"><table class="hg-table"><thead><tr><th scope="col">When</th><th scope="col">Pack</th></tr></thead><tbody>
        <?php foreach ($pack as $r) { ?><tr><td><?= hg_e($r[0]) ?></td><td><?= hg_e($r[1]) ?></td></tr><?php } ?>
        </tbody></table></div>
    </section>

    <?php if ($sample) { ?>
    <section class="hg-answer" id="plan" aria-labelledby="g-plan">
        <h2 class="hg-h2" id="g-plan">A 5-day plan for a first visit</h2>
        <p class="hg-answer__direct">Five days covers Srinagar, Gulmarg and Pahalgam, with a night on a Dal Lake houseboat.</p>
        <ol class="hg-daylist">
            <?php foreach ($sample['itinerary'] as $i => $d) { $h = trim(preg_replace(array('/^Day\s*\d+\s*:?\s*/i', '/\([^)]*\)/'), '', $d['title'])); ?><li><strong>Day <?= $i + 1 ?>:</strong> <?= hg_e(preg_replace('/(\d)\s*Kms\b/i', '$1 km', $h)) ?></li><?php } ?>
        </ol>
        <p><a class="hg-link-arrow" href="<?= hg_e($sample['url']) ?>">See the full day-by-day itinerary <span aria-hidden="true">&rarr;</span></a> · <a href="/tours/kashmir">Compare all Kashmir packages</a></p>
    </section>
    <?php } ?>

    <section class="hg-answer" id="tips" aria-labelledby="g-tips">
        <h2 class="hg-h2" id="g-tips">Practical tips</h2>
        <ul class="hg-checks hg-checks--info"><?php foreach ($c['tips'] as $t) { ?><li><?= hg_e($t) ?></li><?php } ?></ul>
    </section>
</div>
</article>

<?= hg_cta_band('Ready to plan your Kashmir trip?', 'Tell us your dates and who is travelling — we suggest the right itinerary and hotels.') ?>
<?php hg_layout_end(); ?>
