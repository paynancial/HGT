<?php
/** Tab 8 — SEO & AEO (Screen 11). */
$s = $p['seo'];
$site = rtrim(cms_config('site_url'), '/');
$imgs = array('' => 'Featured image (default)');
foreach ($p['media'] as $m) $imgs[$m['media_id']] = $m['alt_text'] ?: basename($m['file_path']);
ob_start(); ?>
<div class="cms-serp" aria-label="Search result preview">
    <p class="cms-serp__url"><?= e($site) ?>/<span data-cms-mirror="slug"><?= e($p['slug']) ?></span></p>
    <p class="cms-serp__title" data-cms-mirror="meta_title"><?= e($s['meta_title'] ?: $p['name']) ?></p>
    <p class="cms-serp__desc" data-cms-mirror="meta_description"><?= e($s['meta_description'] ?: 'Add a meta description.') ?></p>
</div>
<div class="cms-form-grid">
    <?= field_text('meta_title', 'Meta title', $val('meta_title', $s['meta_title']), array('class' => 'cms-span2', 'maxlength' => 70, 'counter' => '30-65', 'hint' => 'Unique, 30–65 characters.')) ?>
    <?= field_text('meta_description', 'Meta description', $val('meta_description', $s['meta_description']), array('type' => 'textarea', 'rows' => 2, 'class' => 'cms-span2', 'maxlength' => 200, 'counter' => '120-160', 'hint' => 'Unique and useful, 120–160 characters.')) ?>
    <?= field_text('slug', 'SEO slug', $val('slug', $p['slug']), array('readonly' => (bool) $p['published_at'], 'hint' => $p['published_at'] ? 'Locked after publishing (a URL change needs a 301 redirect).' : 'Lowercase words joined by hyphens. No .php.')) ?>
    <?= field_text('canonical', 'Canonical URL', $val('canonical', $s['canonical']), array('type' => 'url', 'hint' => 'Absolute https:// URL, no .php. Change only after review.')) ?>
    <?= field_text('og_title', 'OG title', $val('og_title', $s['og_title']), array('hint' => 'Empty = meta title.')) ?>
    <?= field_select('og_media_id', 'OG image', (string) $s['og_media_id'], $imgs) ?>
    <?= field_text('og_description', 'OG description', $val('og_description', $s['og_description']), array('type' => 'textarea', 'rows' => 2, 'class' => 'cms-span2', 'hint' => 'Empty = meta description.')) ?>
</div>
<?php echo card('SEO', ob_get_clean()); ?>

<?php ob_start(); ?>
<div class="cms-form-grid">
    <?= field_text('aeo_question', 'Primary question', $val('aeo_question', $s['aeo_question']), array('class' => 'cms-span2', 'placeholder' => 'A real traveller question, e.g. How many days are enough for Kashmir?')) ?>
    <?= field_text('aeo_answer', 'Direct answer', $val('aeo_answer', $s['aeo_answer']), array('type' => 'textarea', 'rows' => 3, 'class' => 'cms-span2', 'hint' => 'Answer first, in 2–3 factual sentences (15+ words). Only publish what is true for this package.')) ?>
    <?= field_text('key_facts', 'Key facts (one per line)', $val('key_facts', implode("\n", $s['key_facts'])), array('type' => 'textarea', 'rows' => 4, 'placeholder' => "Best time: April–October\nStart: Srinagar airport")) ?>
    <?= field_text('supporting_questions', 'Supporting questions (one per line)', $val('supporting_questions', implode("\n", $s['supporting_questions'])), array('type' => 'textarea', 'rows' => 4)) ?>
</div>
<?php echo card('AEO — answer-first content', ob_get_clean(), array('sub' => 'No keyword stuffing, copied text, city-name substitutions, AI filler, fake reviews or fake statistics.')); ?>

<?php ob_start(); ?>
<ol class="cms-faqs">
<?php foreach ($p['faqs'] as $i => $f) { ?>
    <li class="cms-faq">
        <?= field_text("faqs[$i][question]", 'Question ' . ($i + 1), $f['question'], array('id' => "fq$i")) ?>
        <?= field_text("faqs[$i][answer]", 'Answer', $f['answer'], array('type' => 'textarea', 'rows' => 2, 'id' => "fa$i")) ?>
        <button class="cms-btn cms-btn--ghost cms-btn--sm cms-btn--danger" type="submit" name="op" value="del_faq:<?= $i ?>"><?= icon('trash') ?>Remove</button>
    </li>
<?php } ?>
</ol>
<?php if (!$p['faqs']) echo '<p class="cms-muted">No FAQs yet — at least 2 relevant FAQs are needed to publish.</p>'; ?>
<button class="cms-btn cms-btn--outline" type="submit" name="op" value="add_faq"><?= icon('plus') ?>Add FAQ</button>
<?php echo card('Package FAQs', ob_get_clean(), array('sub' => 'Relevant to this package; no repeated questions.'));
