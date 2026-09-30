<?php
// Not-found page (Phase 1 layout). Also included by templates for unknown slugs.
require_once __DIR__ . '/include/ui/core.php';
http_response_code(404);
hg_layout_start(array(
    'title' => 'Page not found | Holiday Guru Travel',
    'description' => 'The page you were looking for could not be found.',
    'path' => '/404', 'index' => false,
));
?>
<section class="hg-section">
    <div class="hg-container hg-narrow" style="text-align:center">
        <p class="hg-eyebrow">Error 404</p>
        <h1 class="hg-h1">Sorry, we couldn’t find that page</h1>
        <p class="hg-lead" style="margin-left:auto;margin-right:auto">The page may have moved or the link may be mistyped. Search our holidays or try one of these:</p>
        <div class="hg-pagehead__actions" style="justify-content:center;margin-bottom:32px">
            <a class="hg-btn hg-btn--primary" href="/tours">Search all packages</a>
            <a class="hg-btn hg-btn--outline" href="/domestic-holidays">Domestic holidays</a>
            <a class="hg-btn hg-btn--outline" href="/international-holidays">International holidays</a>
            <a class="hg-btn hg-btn--ghost" href="/contact">Contact us</a>
        </div>
    </div>
    <div class="hg-container"><div class="hg-grid hg-grid--dest"><?php foreach (array_slice(array_values(array_filter(hg_groups(), function ($g) { return $g['count'] > 0; })), 0, 8) as $g) echo hg_destination_card($g); ?></div></div>
</section>
<?php hg_layout_end(); ?>
