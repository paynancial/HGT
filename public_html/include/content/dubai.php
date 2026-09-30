<?php
/**
 * Dubai destination content (used by /tours/dubai and Dubai package pages).
 *
 * Sources: Holiday Guru Travel's own Dubai itineraries (routes, distances,
 * inclusions) and well-established destination facts. Rules that change often
 * (visas, permits, registrations) are described generally and confirmed per booking.
 * STATUS 'review': published; owner/travel team to review wording.
 * Last reviewed: 2026-09-30.
 */
return array(
    'status' => 'review',
    'reviewed' => '2026-09-30',
    'name' => 'Dubai',
    'image' => '',
    'map_query' => 'Dubai, United Arab Emirates',
    'related' => array('singapore-malaysia', 'maldives', 'goa', 'kerala'),
    'frequent' => array(
        array('4-day Best Dubai Tour', '/best-dubai-tour'),
        array('5-day Dubai with airfare from Delhi', '/standard-tour-to-dubai-4n5d'),
    ),
    'intro' => 'Dubai tour packages include hotel stays with breakfast, a Dubai city tour with Burj Khalifa, a dhow cruise with dinner and a desert safari with BBQ dinner, with airport transfers. Our Dubai itineraries run 3 to 5 days; two of them include economy airfare from Delhi.',
    'why' => array(
        array(
            'Burj Khalifa',
            'City tours include the observation deck of the world’s tallest building on several packages.',
        ),
        array('Desert safari', 'Dune drive, camp activities and a BBQ dinner in the desert.'),
        array('Dhow cruise', 'An evening cruise with dinner on a traditional wooden dhow.'),
        array('Short flight from India', 'Dubai is a short direct flight from most major Indian cities.'),
    ),
    'best_time' => array(
        array(
            'November – March',
            'Winter',
            'Warm, pleasant days and cool evenings — the best time and the busiest season.',
        ),
        array('April – May', 'Spring', 'Getting hot; good hotel offers.'),
        array('June – September', 'Summer', 'Very hot; indoor attractions, malls and water parks are the focus.'),
        array('October', 'Shoulder', 'Heat eases and outdoor activities restart.'),
    ),
    'best_time_answer' => 'November to March is the best time to visit Dubai; summer (June–September) is very hot.',
    'days_answer' => 'Three to four days covers the city tour, Burj Khalifa, a dhow cruise and a desert safari; allow 5 days to add a day at leisure or an Abu Dhabi excursion.',
    'days_rows' => array(
        array('3 days', 'City tour with Burj Khalifa and dhow cruise', 'best-of-dubai-tour'),
        array('4 days', 'City tour, dhow cruise and desert safari', 'best-dubai-tour'),
        array('5 days', 'With economy airfare from Delhi', 'standard-tour-to-dubai-4n5d'),
        array('5 days', 'Deluxe, with economy airfare from Delhi', 'deluxe-tour-to-dubai-4n5d'),
    ),
    'cost_answer' => 'The cost of a Dubai package depends on the hotel, dates, whether flights are included and which activities you choose. We quote each trip for your dates.',
    'cost_factors' => array(
        'Hotel category and location',
        'With or without airfare from India',
        'Season — December–January and Diwali holidays are peak',
        'Theme parks and optional activities',
        'UAE visa fees, where not included',
        'Private or shared (seat-in-coach) transfers',
    ),
    'places' => array(
        array('Downtown Dubai', 'Burj Khalifa, the Dubai Mall and the Dubai Fountain.'),
        array('Dubai Creek & Marina', 'Dhow cruises with dinner along the creek or marina.'),
        array('The desert', 'Dune drives and a desert camp with BBQ dinner.'),
        array('Old Dubai', 'The gold and spice souks and the Al Fahidi historic district.'),
        array('Dubai Parks and Resorts', 'Theme parks included on our family trip itinerary.'),
    ),
    'things' => array(
        'Visit the Burj Khalifa observation deck',
        'Take a desert safari with BBQ dinner',
        'Cruise on a dhow with dinner',
        'Watch the Dubai Fountain show',
        'Shop the gold and spice souks',
        'Spend a day at the theme parks',
    ),
    'stay' => 'Hotel stays with daily breakfast on twin or double sharing, as listed on each package.',
    'transport' => 'Airport–hotel–airport transfers and sightseeing are included, on a private or shared (seat-in-coach) basis as listed on each package. Selected packages include economy airfare from Delhi, as listed in their inclusions.',
    'who_title' => 'Who Dubai suits',
    'who' => array(
        array(
            'Families',
            'Theme parks, beaches and malls make Dubai one of the easiest family holidays abroad.',
        ),
        array('Couples', 'Desert dinners, the dhow cruise and skyline views suit a short honeymoon.'),
        array(
            'First trip abroad',
            'A short flight and well-organised sightseeing make Dubai an easy first international holiday.',
        ),
    ),
    'tips' => array(
        'Most Indian passport holders need a UAE visa arranged before travel; we confirm current rules with your booking.',
        'Dress modestly in public places such as malls and souks, and cover up when visiting mosques.',
        'Carry sunglasses and sunscreen, even in winter.',
        'Book Burj Khalifa time slots early for sunset.',
    ),
    'faqs' => array(
        array(
            'What is included in Dubai tour packages?',
            '<p>Hotel stays with breakfast, airport transfers, and the sightseeing listed — typically a city tour with Burj Khalifa, a dhow cruise with dinner and a desert safari with dinner. Selected packages also include economy airfare from Delhi, as listed in their inclusions.</p>',
        ),
        array(
            'Are flights included?',
            '<p>Only in the packages that say so (our standard and deluxe 5-day tours include economy airfare Delhi–Dubai–Delhi). Others start on arrival in Dubai.</p>',
        ),
        array(
            'Is the UAE visa included?',
            '<p>Only where a package lists it. We confirm current visa requirements for your passport with your booking.</p>',
        ),
        array(
            'How many days are enough for Dubai?',
            '<p>Three to four days for the main sights; five days for a more relaxed trip.</p>',
        ),
        array('What is the best time to visit Dubai?', '<p>November to March.</p>'),
        array(
            'Can Dubai packages be customized?',
            '<p>Yes. <a href="/customized-holidays?destination=Dubai">Tell us what you need</a>.</p>',
        ),
    ),
);
