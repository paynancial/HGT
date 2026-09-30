<?php
/**
 * Policy pages (cancellation, refund, payment). The wording restates the terms
 * already printed on Holiday Guru Travel's package pages; nothing is added.
 * STATUS: draft — noindex until the owner (and legal adviser) approves.
 */
require_once __DIR__ . '/../ui/core.php';

if (!function_exists('hg_render_policy')) {
    function hg_render_policy(array $meta, $bodyHtml)
    {
        $status = hg_page_status($meta['path']);
        hg_layout_start(array(
            'title' => $meta['title'], 'description' => $meta['description'], 'path' => $meta['path'],
            'index' => $status === 'approved',
            'breadcrumbs' => array(array('Home', '/'), array($meta['h1'], null)),
        ));
        ?>
<section class="hg-pagehead" aria-labelledby="page-title">
    <div class="hg-container hg-narrow">
        <p class="hg-eyebrow">Legal &amp; support</p>
        <h1 class="hg-h1" id="page-title"><?= hg_e($meta['h1']) ?></h1>
        <p class="hg-lead"><?= hg_e($meta['lead']) ?></p>
    </div>
</section>
<section class="hg-section hg-section--tight">
    <div class="hg-container hg-narrow hg-prose hg-policy">
        <?= $bodyHtml ?>
        <div class="hg-notice" role="note"><p>The terms shown on your package page and in your booking confirmation apply to your booking. Questions? <a href="/contact">Contact us</a> or call <a href="<?= hg_e(hg_tel_href()) ?>"><?= hg_e(HG_PHONE_DISPLAY) ?></a>.</p></div>
        <p class="hg-muted" style="font-size:14px">Holiday Guru Travel is operated by <?= hg_e(HG_LEGAL_NAME) ?>.</p>
    </div>
</section>
<?php
        hg_layout_end();
    }
}
