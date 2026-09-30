<?php
/**
 * GLOBAL COMPONENT: header Holiday Package Search (#hg-header-search).
 * Sends its state to the search layer (/tours) as query parameters; it never injects
 * content into page templates. Behaviour: [data-hg-search-*] in assets/js/hg-ui.js.
 */
$hgS = hg_search_state();
if ($hgS['destination'] === '' && !empty($GLOBALS['hgMeta']['search_destination'])) {
    $hgS['destination'] = $GLOBALS['hgMeta']['search_destination']; // e.g. Kashmir on a Kashmir package page
}
?>
        <form class="hg-hsearch" role="search" action="/tours" method="get" data-hg-search-form id="hg-header-search" aria-label="Search holiday packages">
            <div class="hg-hsearch__sheethead">
                <span class="hg-hsearch__sheettitle">Search holiday packages</span>
                <button type="button" class="hg-iconbtn" data-hg-search-close><?= hg_icon('close') ?><span class="hg-sr">Close search</span></button>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--dest">
                <?= hg_icon('search') ?>
                <label for="hg-q">Destination</label>
                <input id="hg-q" name="destination" type="search" value="<?= hg_e($hgS['destination']) ?>" placeholder="Search &quot;Gulmarg&quot;" autocomplete="off"
                       data-hg-placeholder-cycle="Gulmarg|Kashmir|Char Dham|Kerala|Dubai|Leh Ladakh|Goa"
                       role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="hg-q-list" data-hg-autocomplete data-hg-ac-fill>
                <ul class="hg-ac" id="hg-q-list" role="listbox" aria-label="Suggestions" hidden></ul>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--date hg-hsearch__field--extra">
                <?= hg_icon('calendar') ?>
                <label for="hg-date">Travel date</label>
                <input id="hg-date" name="date" type="date" value="<?= hg_e($hgS['date']) ?>" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--extra">
                <?= hg_icon('users') ?>
                <label for="hg-adults">Adults</label>
                <select id="hg-adults" name="adults"<?= $hgS['has_travellers'] ? '' : ' data-hg-optional' ?>><?php for ($i = 1; $i <= 9; $i++) { ?><option value="<?= $i ?>"<?= $i === $hgS['adults'] ? ' selected' : '' ?>><?= $i ?> Adult<?= $i > 1 ? 's' : '' ?></option><?php } ?></select>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--extra">
                <?= hg_icon('users') ?>
                <label for="hg-children">Children</label>
                <select id="hg-children" name="children"<?= $hgS['has_travellers'] ? '' : ' data-hg-optional' ?>><?php for ($i = 0; $i <= 6; $i++) { ?><option value="<?= $i ?>"<?= $i === $hgS['children'] ? ' selected' : '' ?>><?= $i ?></option><?php } ?></select>
            </div>
            <div class="hg-hsearch__field hg-hsearch__field--extra">
                <?= hg_icon('route') ?>
                <label for="hg-departure">Departure city</label>
                <select id="hg-departure" name="departure"><option value="">Any</option><option value="delhi"<?= $hgS['departure'] === 'delhi' ? ' selected' : '' ?>>Delhi</option><option value="haridwar"<?= $hgS['departure'] === 'haridwar' ? ' selected' : '' ?>>Haridwar</option></select>
            </div>
            <button class="hg-btn hg-btn--primary hg-hsearch__submit" type="submit"><?= hg_icon('search') ?><span>Search</span><span class="hg-sr"> holiday packages</span></button>
        </form>
