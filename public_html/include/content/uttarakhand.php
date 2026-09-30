<?php
/**
 * Uttarakhand destination content (used by /tours/uttarakhand and Uttarakhand package pages).
 *
 * Sources: Holiday Guru Travel's own Uttarakhand itineraries (routes, distances,
 * inclusions) and well-established destination facts. Rules that change often
 * (visas, permits, registrations) are described generally and confirmed per booking.
 * STATUS 'review': published; owner/travel team to review wording.
 * Last reviewed: 2026-09-30.
 */
return array(
    'status' => 'review',
    'reviewed' => '2026-09-30',
    'name' => 'Uttarakhand',
    'image' => '',
    'map_query' => 'Nainital, Uttarakhand',
    'related' => array('char-dham', 'himachal', 'kashmir', 'ladakh'),
    'frequent' => array(
        array('Char Dham Yatra packages', '/tours/char-dham'),
        array('4-day Corbett with Nainital itinerary', '/corbett-with-nainital-04-days'),
    ),
    'intro' => 'Uttarakhand tour packages combine the lake town of Nainital, Mussoorie and the Jim Corbett National Park with Kumaon hill stations such as Ranikhet, Kausani and Almora, and the Ganga towns of Haridwar and Rishikesh. Our Uttarakhand itineraries run 3 to 8 days by private cab.',
    'why' => array(
        array(
            'Close to Delhi',
            'Mussoorie is about 270 km and Nainital about 320 km from Delhi by road — ideal for short breaks.',
        ),
        array(
            'Lakes and hill stations',
            'Boating on Naini Lake, Mussoorie’s Mall Road and Kempty Falls, and Himalayan views from Kausani.',
        ),
        array('Wildlife', 'Jim Corbett, India’s first national park, in the Himalayan foothills.'),
        array(
            'Spiritual towns',
            'The Ganga aarti at Har Ki Pauri in Haridwar and the ghats and bridges of Rishikesh.',
        ),
    ),
    'best_time' => array(
        array(
            'March – June',
            'Spring & summer',
            'The main season for Nainital and Mussoorie; pleasant days and busy weekends.',
        ),
        array(
            'July – September',
            'Monsoon',
            'Heavy rain and possible landslides; some Corbett safari zones close for the monsoon.',
        ),
        array(
            'October – November',
            'Autumn',
            'Clear skies and the best Himalayan views from Kausani and Ranikhet.',
        ),
        array(
            'December – February',
            'Winter',
            'Cold, with occasional snow in Nainital and Mussoorie; quieter and good value.',
        ),
    ),
    'best_time_answer' => 'March to June and October to November are the best times for Uttarakhand hill stations; October to June suits a Corbett safari.',
    'days_answer' => 'Three days is enough for one hill station such as Nainital or Mussoorie; allow 5–6 days to combine Nainital with Jim Corbett and Kausani or Ranikhet, and 7–8 days for a wider Kumaon and Garhwal circuit.',
    'days_rows' => array(
        array('3 days', 'Nainital', 'nainital-tour-03-days'),
        array('4 days', 'Jim Corbett and Nainital', 'corbett-with-nainital-04-days'),
        array('5 days', 'Nainital, Kausani and Jim Corbett', 'nainital-with-kausani-and-jim-corbett-05-days'),
        array(
            '7 days',
            'Haridwar, Rishikesh, Mussoorie, Nainital and Corbett',
            'haridwar-with-rishikesh-mussoorie-nainital-and-jim-corbett-07-days',
        ),
        array(
            '8 days',
            'Mussoorie, Corbett, Ranikhet, Kausani and Nainital',
            'mussoorie-with-corbett-ranikhet-kausani-and-nainital-08-days',
        ),
    ),
    'cost_answer' => 'The cost of an Uttarakhand tour depends on the number of nights, hotel category, season and whether you add a Corbett safari. We quote each trip for your dates.',
    'cost_factors' => array(
        'Number of hill stations and nights',
        'Hotel category (deluxe on twin sharing in many packages)',
        'Season — May–June and long weekends are peak',
        'Jungle safari in Corbett, booked separately and subject to availability',
        'Number of travellers sharing a room and cab',
        'GST where a package lists it as extra',
    ),
    'places' => array(
        array(
            'Nainital',
            'Naini Lake boating, the Mall Road, Snow View point and nearby lakes; about 320 km (9 hours) from Delhi.',
        ),
        array(
            'Mussoorie',
            'The Mall Road, Gun Hill and Kempty Falls, about 15 km away; about 270 km (8 hours) from Delhi.',
        ),
        array(
            'Jim Corbett',
            'India’s first national park, spread over about 520 sq km in the Himalayan foothills; safaris by jeep or canter.',
        ),
        array(
            'Kausani & Ranikhet',
            'Quiet Kumaon hill stations with wide Himalayan views; Kausani is about 125 km (4 hours) from Nainital.',
        ),
        array(
            'Haridwar & Rishikesh',
            'The evening Ganga aarti at Har Ki Pauri, and the ghats, temples and suspension bridges of Rishikesh.',
        ),
    ),
    'things' => array(
        'Go boating on Naini Lake',
        'Take a jungle safari in Jim Corbett (subject to permits)',
        'See the Himalayan panorama from Kausani',
        'Watch the Ganga aarti at Har Ki Pauri, Haridwar',
        'Visit Kempty Falls near Mussoorie',
        'Walk the ghats and bridges of Rishikesh',
    ),
    'stay' => 'Hotels as listed in each itinerary, deluxe category on twin sharing in many packages, with the meals shown on each package page.',
    'transport' => 'Our Uttarakhand packages use a private cab for transfers and sightseeing, with pick-up from the railway station, bus stand or airport as listed. Tolls, parking and driver allowance are included in many packages.',
    'who_title' => 'Who Uttarakhand suits',
    'who' => array(
        array(
            'Families',
            'Short drives from Delhi, lake boating and a wildlife safari make Uttarakhand an easy family holiday.',
        ),
        array(
            'Couples',
            'Quiet Kausani and Ranikhet and lakeside Nainital suit couples looking for a calm break.',
        ),
        array('Nature lovers', 'Corbett’s wildlife, the Kumaon forests and Himalayan sunrise views.'),
    ),
    'tips' => array(
        'Corbett safaris need permits and are limited per day; book early and note that some zones close in the monsoon.',
        'Hill roads are winding — allow extra time and carry motion-sickness medicine if needed.',
        'Book early for May–June and long weekends, when Nainital and Mussoorie fill up.',
        'Carry a light jacket even in summer; evenings are cool.',
    ),
    'faqs' => array(
        array(
            'What is included in Uttarakhand tour packages?',
            '<p>Hotel stays as per the itinerary, a private cab for transfers and sightseeing, and — in many packages — tolls, parking, driver allowance and applicable taxes. Each package lists its exact inclusions and exclusions.</p>',
        ),
        array(
            'How many days are enough for Nainital?',
            '<p>Two to three days for Nainital itself; add two or three days for Jim Corbett or Kausani.</p>',
        ),
        array(
            'Is the Corbett safari included?',
            '<p>Only where a package lists it. Safari permits are limited and booked separately, subject to availability.</p>',
        ),
        array(
            'What is the best time to visit Uttarakhand?',
            '<p>March to June and October to November for the hill stations; October to June for Corbett.</p>',
        ),
        array(
            'Do you have Char Dham packages?',
            '<p>Yes — see our <a href="/tours/char-dham">Char Dham Yatra packages</a>.</p>',
        ),
        array(
            'Can Uttarakhand packages be customized?',
            '<p>Yes. <a href="/customized-holidays?destination=Uttarakhand">Tell us what you need</a>.</p>',
        ),
    ),
);
