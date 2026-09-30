<!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Sign in · Holiday Guru CMS</title>
<link rel="stylesheet" href="/cms-assets/cms.css?v=<?= filemtime(CMS_ROOT . '/public/cms-assets/cms.css') ?>">
</head>
<body class="cms cms-login">
<?php if (cms_is_staging()) { ?><div class="cms-ribbon" role="note">Staging CMS — changes here do not reach the live website</div><?php } ?>
<main class="cms-login__wrap">
    <form class="cms-login__card" method="post" action="/login<?= get('next') ? '?next=' . e(rawurlencode(get('next'))) : '' ?>">
        <img src="/assets/brand/holiday-guru-travel-logo-240.png" alt="Holiday Guru Travel" width="160" height="53">
        <h1>Sign in to the CMS</h1>
        <p class="cms-muted">Tour packages, offers and enquiries.</p>
        <?php if ($error) { ?><p class="cms-flash cms-flash--err" role="alert"><?= e($error) ?></p><?php } ?>
        <?= csrf_field() ?>
        <?= field_text('email', 'Email', $email, array('type' => 'email', 'required' => true, 'autocomplete' => 'username')) ?>
        <?= field_text('password', 'Password', '', array('type' => 'password', 'required' => true, 'autocomplete' => 'current-password')) ?>
        <button class="cms-btn cms-btn--primary cms-btn--block" type="submit">Sign in</button>
    </form>
</main>
</body>
</html>
