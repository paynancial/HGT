<?php
/**
 * Leadership and team profiles — to be supplied by the owner.
 * Fill name, role and a 1–2 sentence bio; add the photo file under
 * assets/img/team/ and set 'photo' (e.g. 'assets/img/team/name.jpg').
 * Leave a field empty to show "To be added". Add or remove entries freely.
 */
$blank = array('name' => '', 'role' => '', 'bio' => '', 'photo' => '');
return array(
    'leadership' => array($blank, $blank, $blank),
    'team' => array($blank, $blank, $blank, $blank, $blank, $blank),
);
