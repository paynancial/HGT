<?php
/**
 * GLOBAL SHELL (top): skip link, utility bar, header (logo, search, contact actions, login), navigation.
 * Each part is its own component in include/global/. Nothing page-specific is rendered here.
 */
require_once __DIR__ . '/../ui/core.php';
?>
<a class="hg-skip" href="#main">Skip to content</a>
<?php include __DIR__ . '/utility-bar.php'; ?>

<header class="hg-header" data-hg-header>
    <div class="hg-container hg-header__inner">
        <button type="button" class="hg-iconbtn hg-header__menu" aria-controls="hg-nav" aria-expanded="false" data-hg-menu-toggle>
            <?= hg_icon('menu') ?><span class="hg-sr">Open menu</span>
        </button>
        <a class="hg-header__logo" href="/" aria-label="Holiday Guru Travel home">
            <picture>
                <source type="image/webp" srcset="/assets/brand/holiday-guru-travel-logo-240.webp 1x, /assets/brand/holiday-guru-travel-logo-480.webp 2x">
                <img src="/assets/brand/holiday-guru-travel-logo-240.png" alt="Holiday Guru Travel" width="122" height="60">
            </picture>
        </a>
<?php include __DIR__ . '/holiday-search.php'; ?>
        <div class="hg-header__actions">
            <button type="button" class="hg-iconbtn hg-header__searchbtn" aria-controls="hg-header-search" aria-expanded="false" data-hg-search-toggle><?= hg_icon('search') ?><span class="hg-sr">Search holiday packages</span></button>
            <a class="hg-hcontact" href="<?= hg_e(hg_tel_href()) ?>" aria-label="Call <?= hg_e(HG_PHONE_DISPLAY) ?>"><?= hg_icon('phone') ?><span><strong>Call Now</strong><?= hg_e(HG_PHONE_DISPLAY) ?></span></a>
            <a class="hg-hcontact hg-hcontact--wa" href="<?= hg_e(hg_whatsapp_href()) ?>" target="_blank" rel="noopener" aria-label="WhatsApp <?= hg_e(HG_PHONE_DISPLAY) ?>"><?= hg_icon('whatsapp') ?><span><strong>WhatsApp</strong><?= hg_e(HG_PHONE_DISPLAY) ?></span></a>
            <button type="button" class="hg-iconbtn hg-header__login" data-hg-login-open><?= hg_icon('user') ?><span class="hg-sr">Login</span></button>
        </div>
    </div>

<?php include __DIR__ . '/navigation.php'; ?>
</header>
