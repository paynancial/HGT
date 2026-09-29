<?php http_response_code(404); ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <base href="/">
    <title>Page not found | Holiday Guru Travel</title>
    <meta name="robots" content="noindex,follow">
    <meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
</head>
<body>
    <?php
    include __DIR__ . "/include/header.php";
    ?>
    <section class="space">
        <div class="container text-center">
            <h1 class="sec-title">Sorry, we couldn't find that page</h1>
            <p class="sec-text mb-30">The page may have moved or the link may be mistyped. These may help:</p>
            <div class="d-flex flex-wrap justify-content-center gap-3 mb-30">
                <a href="domestic-holidays.php" class="th-btn style3">India Tours</a>
                <a href="international-holidays.php" class="th-btn style3">International Tours</a>
                <a href="honeymoon-holiday.php" class="th-btn style3">Honeymoon</a>
                <a href="destinations.php" class="th-btn style3">All Destinations</a>
            </div>
            <p class="sec-text">Or talk to a travel expert: <a href="tel:+919971754265">+91 99717 54265</a> &middot; <a href="mailto:sales@holidaygurutravel.in">sales@holidaygurutravel.in</a></p>
            <p><a href="index.php">&larr; Back to home</a></p>
        </div>
    </section>
    <?php
    include __DIR__ . "/include/footer.php";
    ?>
</body>
</html>
