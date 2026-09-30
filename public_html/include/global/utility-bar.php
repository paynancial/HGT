<?php
/**
 * GLOBAL COMPONENT: utility bar (tagline, Login). Owns nothing page-specific.
 * Included by include/global/header.php. Styles: .hg-utility (assets/css/src/components/utility-bar.css).
 */
?>
<div class="hg-utility">
    <div class="hg-container hg-utility__inner">
        <p class="hg-utility__tagline">Premium Holiday Planner</p>
        <ul class="hg-utility__right">
            <li><button type="button" class="hg-login-btn" data-hg-login-open><?= hg_icon('user') ?><span>Login</span></button></li>
        </ul>
    </div>
</div>
