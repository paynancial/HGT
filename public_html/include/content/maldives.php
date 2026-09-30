<?php
/**
 * Maldives destination content (used by /tours/maldives and Maldives package pages).
 *
 * Sources: Holiday Guru Travel's own Maldives itineraries (routes, distances,
 * inclusions) and well-established destination facts. Rules that change often
 * (visas, permits, registrations) are described generally and confirmed per booking.
 * STATUS 'review': published; owner/travel team to review wording.
 * Last reviewed: 2026-09-30.
 */
return array(
    'status' => 'review',
    'reviewed' => '2026-09-30',
    'name' => 'Maldives',
    'image' => '',
    'map_query' => 'Maldives',
    'related' => array('dubai', 'singapore-malaysia', 'goa', 'kerala'),
    'frequent' => array(
        array('5-day Maldives beach and water villa', '/maldives-05-days'),
    ),
    'intro' => 'Our Maldives package is a 4-night, 5-day resort holiday with two nights in a beach villa and two in a water villa, full-board meals, and seaplane, domestic flight or speedboat transfers as set by the resort, with Maldivian taxes and green tax included.',
    'why' => array(
        array('Beach and water villa', 'Two nights on the beach and two over the lagoon in one stay.'),
        array('Full board', 'Breakfast, lunch and dinner are included.'),
        array('Taxes included', 'The package includes the green tax, Maldivian tax and GST.'),
        array('Short flight from India', 'Malé is a short direct flight from several Indian cities.'),
    ),
    'best_time' => array(
        array('December – April', 'Dry season', 'Sunny, calm seas and the peak season.'),
        array('May – November', 'Wet season', 'More rain and wind, with good offers; showers are usually short.'),
    ),
    'best_time_answer' => 'December to April is the best time for the Maldives; May to November is wetter but good value.',
    'days_answer' => 'Four nights is ideal for a Maldives resort stay, splitting time between a beach villa and a water villa.',
    'days_rows' => array(
        array('5 days', '2 nights beach villa + 2 nights water villa, full board', 'maldives-05-days'),
    ),
    'cost_answer' => 'The cost of a Maldives holiday depends mainly on the resort, the villa type, the transfer mode and the dates. We quote each stay for your dates; international flights are extra.',
    'cost_factors' => array(
        'Resort and villa category',
        'Transfer by seaplane, domestic flight or speedboat',
        'Meal plan (our package is full board)',
        'Season — December–April and holidays are peak',
        'International flights to Malé',
        'Optional excursions and spa treatments',
    ),
    'places' => array(
        array('Malé', 'The capital and international airport, where every trip begins.'),
        array(
            'Your resort island',
            'Most resorts occupy their own island, reached by seaplane, domestic flight or speedboat.',
        ),
        array(
            'The lagoon',
            'Snorkelling, kayaking and water sports from the resort, subject to the resort’s rules.',
        ),
    ),
    'things' => array(
        'Stay in a water villa over the lagoon',
        'Snorkel the house reef',
        'Take a seaplane transfer (resort-dependent)',
        'Watch sunset from the beach villa',
        'Book a sandbank or dolphin excursion',
    ),
    'stay' => 'Two nights in a beach villa and two in a water villa, with full-board meals and a welcome drink, as listed on the package.',
    'transport' => 'Resort transfers by seaplane, domestic flight or speedboat, depending on the resort. International flights to Malé are not included.',
    'who_title' => 'Who it suits',
    'who' => array(
        array('Couples', 'Water villas and quiet islands make the Maldives a classic honeymoon.'),
        array(
            'Families',
            'Choose a resort with a kids’ club; note the package terms say no extra bed for children.',
        ),
        array('Relaxation', 'Few distractions — just the lagoon, reef and beach.'),
    ),
    'tips' => array(
        'The Maldives gives a free 30-day tourist visa on arrival to most visitors with a confirmed resort booking; we confirm current rules with your booking.',
        'Seaplanes fly only in daylight — late arrivals may need a night near Malé.',
        'Resort activities and excursions depend on each resort’s rules.',
    ),
    'faqs' => array(
        array(
            'What is included in the Maldives package?',
            '<p>Two nights in a beach villa and two in a water villa, full-board meals, a welcome drink, resort transfers (seaplane, domestic flight or speedboat) and the green tax, Maldivian tax and GST.</p>',
        ),
        array('Are international flights included?', '<p>No. We can add flights to Malé to your quote.</p>'),
        array('What is the best time to visit the Maldives?', '<p>December to April.</p>'),
        array(
            'Is there an extra bed for children?',
            '<p>The package terms state no extra bed for kids; ask us about family villas.</p>',
        ),
        array(
            'Can I choose a different resort?',
            '<p>Yes. <a href="/customized-holidays?destination=Maldives">Tell us what you need</a>.</p>',
        ),
    ),
);
