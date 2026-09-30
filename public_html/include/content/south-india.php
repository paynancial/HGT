<?php
/**
 * Ooty, Mysore & Coorg destination content (used by /tours/south-india and Ooty, Mysore & Coorg package pages).
 *
 * Sources: Holiday Guru Travel's own Ooty, Mysore & Coorg itineraries (routes, distances,
 * inclusions) and well-established destination facts. Rules that change often
 * (visas, permits, registrations) are described generally and confirmed per booking.
 * STATUS 'review': published; owner/travel team to review wording.
 * Last reviewed: 2026-09-30.
 */
return array(
    'status' => 'review',
    'reviewed' => '2026-09-30',
    'name' => 'Ooty, Mysore & Coorg',
    'image' => '',
    'map_query' => 'Mysore, Karnataka',
    'related' => array('kerala', 'goa', 'sikkim-darjeeling', 'uttarakhand'),
    'frequent' => array(
        array('5-day Bangalore, Mysore and Ooty itinerary', '/bangalore-mysore-ooty-tour-05-days'),
        array('Kerala tour packages', '/tours/kerala'),
    ),
    'intro' => 'Ooty, Mysore and Coorg tour packages start in Bangalore and combine Mysore’s palace with the Nilgiri hills at Ooty and Coonoor, the coffee country of Coorg, Wayanad or Kodaikanal. Our itineraries run 4 to 6 days by private cab.',
    'why' => array(
        array('Royal Mysore', 'Mysore Palace, Chamundi Hills and the Brindavan Gardens.'),
        array('Nilgiri hills', 'Ooty and Coonoor: tea gardens, lakes and the Nilgiri Mountain Railway.'),
        array('Coffee country', 'Coorg’s coffee estates, waterfalls and misty hills.'),
        array(
            'Easy from Bangalore',
            'Mysore is about 160 km from Bangalore, and every tour begins and ends there.',
        ),
    ),
    'best_time' => array(
        array(
            'October – March',
            'Cool season',
            'Pleasant weather across Mysore, Coorg and the hills; the main season.',
        ),
        array('April – June', 'Summer', 'Warm in Mysore and Bangalore; Ooty and Kodaikanal are at their busiest.'),
        array('July – September', 'Monsoon', 'Green and misty, with heavy rain in Coorg and Wayanad.'),
    ),
    'best_time_answer' => 'October to March is the best time to visit Mysore and Coorg; April to June is peak season in Ooty.',
    'days_answer' => 'Four days covers Mysore with Coorg or Wayanad; allow 5 days for Mysore and Ooty, and 6 days to add Kodaikanal.',
    'days_rows' => array(
        array('4 days', 'Mysore and Coorg', 'mysore-coorg-04-days'),
        array('4 days', 'Mysore and Wayanad', 'mysore-wayand-04-days'),
        array('5 days', 'Bangalore, Mysore and Ooty', 'bangalore-mysore-ooty-tour-05-days'),
        array('6 days', 'Mysore, Ooty and Kodaikanal', 'mysore-ooty-kodaikanal-06-days'),
    ),
    'cost_answer' => 'The cost depends on the number of nights, hotel category and season. We quote each trip for your dates.',
    'cost_factors' => array(
        'Number of nights and places',
        'Hotel category',
        'Season — Ooty is peak in April–June',
        'Entry fees and optional rides, paid locally',
        'Flights or trains to Bangalore',
        'GST where a package lists it as extra',
    ),
    'places' => array(
        array(
            'Mysore',
            'Mysore Palace, Chamundi Hills, St Philomena’s Church and the Brindavan Gardens; about 160 km from Bangalore.',
        ),
        array('Ooty & Coonoor', 'The Botanical Garden, Ooty Lake, Doddabetta peak and Coonoor’s tea gardens.'),
        array(
            'Coorg',
            'Coffee plantations, Abbey Falls and the Tibetan monastery at Bylakuppe; about 135 km from Mysore.',
        ),
        array('Wayanad', 'Forests, waterfalls and viewpoints in the Western Ghats; about 150 km from Mysore.'),
        array('Kodaikanal', 'A lake town in the Palani hills with forest walks and viewpoints.'),
    ),
    'things' => array(
        'Tour Mysore Palace',
        'Ride the Nilgiri Mountain Railway (tickets subject to availability)',
        'Walk through Coorg’s coffee estates',
        'Visit the Botanical Garden and lake in Ooty',
        'See the Tibetan settlement at Bylakuppe',
        'Boat on Kodaikanal Lake',
    ),
    'stay' => 'Hotels as listed in each itinerary, deluxe category on twin sharing in some packages.',
    'transport' => 'Tours start and end in Bangalore (or Mysore), with pick-up from the airport, railway station or bus stand and a private cab for transfers and sightseeing.',
    'who_title' => 'Who it suits',
    'who' => array(
        array('Families', 'Palaces, gardens and a toy train make this an easy family circuit.'),
        array('Couples', 'Coorg’s plantation stays and Ooty’s hills are popular with honeymooners.'),
        array('Nature lovers', 'Waterfalls, forests and tea country across the Western Ghats.'),
    ),
    'tips' => array(
        'Book Nilgiri Mountain Railway tickets early; they sell out in season.',
        'Carry a jacket for Ooty and Kodaikanal evenings.',
        'Hill roads to Ooty and Kodaikanal have many hairpin bends — plan for slow driving.',
    ),
    'faqs' => array(
        array(
            'What is included in these packages?',
            '<p>Hotel stays as per the itinerary, pick-up and drop, and a private cab for transfers and sightseeing. Each package lists its exact inclusions and exclusions.</p>',
        ),
        array('Where do these tours start?', '<p>In Bangalore, or in Mysore on some itineraries.</p>'),
        array(
            'How many days are enough?',
            '<p>Four days for Mysore with Coorg or Wayanad; five to six days to add Ooty or Kodaikanal.</p>',
        ),
        array(
            'What is the best time to visit?',
            '<p>October to March; April to June is peak season in Ooty.</p>',
        ),
        array(
            'Can I combine this with Kerala?',
            '<p>Yes — Ooty and Wayanad connect easily to Kerala. See <a href="/tours/kerala">Kerala packages</a> or <a href="/customized-holidays">ask us</a>.</p>',
        ),
        array(
            'Can the itinerary be customized?',
            '<p>Yes. <a href="/customized-holidays?destination=Ooty%2C%20Mysore%20%26%20Coorg">Tell us what you need</a>.</p>',
        ),
    ),
);
