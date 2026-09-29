<?php
// International holiday packages (Phase 1 region template).
require __DIR__ . '/include/templates/region.php';
hg_render_region('international', array(
    'path' => '/international-holidays',
    'title' => 'Explore the World: International Holiday Packages by Holiday Guru Travel',
    'description' => "Plan your next adventure with Holiday Guru Travel's international holiday packages. Explore diverse cultures, breathtaking landscapes, and iconic landmarks. Start your journey today!",
    'h1' => 'International Holiday Packages',
    'lead' => 'Dubai, Singapore & Malaysia and the Maldives, with hotels, transfers and sightseeing planned together.',
    'soon' => array('Thailand', 'Europe', 'Bali'),
));
