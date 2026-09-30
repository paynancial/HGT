<?php
// Human-readable sitemap (the XML sitemap for search engines is /sitemap.xml).
require __DIR__ . '/include/ui/core.php';

$groups = array_values(array_filter(hg_groups(), function ($g) { return $g['count'] > 0; }));
$main = array(
    array('Home', '/'), array('Domestic holidays', '/domestic-holidays'), array('International holidays', '/international-holidays'),
    array('India tours for foreign travellers', '/india-tours'), array('Pilgrimage tours', '/religious-tour'), array('Customised tours', '/customized-holidays'),
    array('Search all packages', '/tours'), array('Car rental', '/service'), array('About us', '/about'), array('Contact us', '/contact'),
);
$help = array(array('FAQs', '/faqs'), array('Kashmir travel guide', '/travel-guide/kashmir'), array('Cancellation policy', '/cancellation-policy'), array('Refund policy', '/refund-policy'), array('Payment policy', '/payment-policy'));
$link = function ($l) { return hg_page_exists($l[1]) && hg_page_status($l[1]) === 'approved' ? '<li><a href="' . hg_e($l[1]) . '">' . hg_e($l[0]) . '</a></li>' : ''; };

hg_layout_start(array(
    'title' => 'Sitemap | Holiday Guru Travel',
    'description' => 'All pages on the Holiday Guru Travel website: destinations, holiday packages, services and help pages.',
    'path' => '/sitemap',
    'breadcrumbs' => array(array('Home', '/'), array('Sitemap', null)),
));
?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container"><h1 class="hg-h1" id="page-title">Sitemap</h1><p class="hg-lead">Every page on the site, grouped by destination.</p></div>
</section>
<section class="hg-section hg-section--tight">
    <div class="hg-container hg-sitemap">
        <div class="hg-grid hg-grid--3">
            <div><h2 class="hg-h3">Main pages</h2><ul class="hg-linklist"><?= implode('', array_map($link, $main)) ?></ul></div>
            <div><h2 class="hg-h3">Destinations</h2><ul class="hg-linklist"><?php foreach ($groups as $g) { ?><li><a href="<?= hg_e($g['hub_url']) ?>"><?= hg_e($g['name']) ?></a></li><?php } ?></ul></div>
            <div><h2 class="hg-h3">Help &amp; policies</h2><ul class="hg-linklist"><?= implode('', array_map($link, $help)) ?></ul></div>
        </div>
        <h2 class="hg-h2" style="margin-top:48px">Holiday packages</h2>
        <div class="hg-grid hg-grid--3">
            <?php foreach ($groups as $g) { $pk = hg_packages_in($g['key']); usort($pk, function ($a, $b) { return $a['days'] <=> $b['days']; }); ?>
            <div>
                <h3 class="hg-h3" style="font-size:17px"><a href="<?= hg_e($g['hub_url']) ?>"><?= hg_e($g['name']) ?></a></h3>
                <ul class="hg-sitemap__list"><?php foreach ($pk as $p) { ?><li><a href="<?= hg_e($p['url']) ?>"><?= hg_e($p['title']) ?></a> <span class="hg-muted">· <?= hg_e($p['duration']) ?></span></li><?php } ?></ul>
            </div>
            <?php } ?>
        </div>
    </div>
</section>
<?php hg_layout_end(); ?>
