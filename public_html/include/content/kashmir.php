<?php
/**
 * Kashmir destination content (used by /tours/kashmir, the Kashmir travel
 * guide and Kashmir package pages).
 *
 * Sources: Holiday Guru Travel's own Kashmir itineraries (distances, routes,
 * inclusions, optional costs) and well-established destination facts.
 * STATUS 'review': published; owner/travel team to review wording.
 * Last reviewed: 2026-09-29 (initial draft).
 */
return array(
    'status' => 'review',
    'reviewed' => '2026-09-29',
    'name' => 'Kashmir',
    'region_label' => 'India',
    'region_url' => '/domestic-holidays',
    'image' => 'assets/img/destination/SrinagarGulmargPahalgamTour2.jpg',
    'map_query' => 'Srinagar, Jammu and Kashmir',
    'related' => array('amarnath', 'ladakh', 'himachal', 'uttarakhand'),
    'frequent' => array(
        array('5-day Srinagar, Gulmarg & Pahalgam itinerary', '/srinagar-gulmarg-pahalgam-tour-package-5-days'),
        array('Kashmir travel guide', '/travel-guide/kashmir'),
        array('Amarnath Yatra packages', '/tours/amarnath'),
    ),
    'more_html' => 'For month-by-month advice, how to reach and packing tips, read our <a href="/travel-guide/kashmir">Kashmir travel guide</a>. Planning the pilgrimage? See <a href="/tours/amarnath">Amarnath Yatra packages</a>.',

    'intro' => 'Kashmir tour packages combine Srinagar’s Dal Lake and Mughal gardens with day trips or stays in Gulmarg, Pahalgam and Sonmarg, plus private cab transfers from Srinagar airport. Our Kashmir itineraries run 4 to 8 days; several include a night on a houseboat, and one continues to Katra for Mata Vaishno Devi.',

    'why' => array(
        array('Lakes, meadows and mountains', 'Dal Lake shikara rides, the meadows of Gulmarg and Sonmarg, and the Lidder valley at Pahalgam — all within a day’s drive of Srinagar.'),
        array('A different trip each season', 'Tulips and blossom in spring, green meadows in summer, chinar colours in autumn and snow in Gulmarg in winter.'),
        array('Works for most travellers', 'Mostly road trips with hotel or houseboat stays and short walks; ponies and cable cars are available for the higher points.'),
        array('Culture and heritage', 'Mughal gardens, the Shankaracharya temple viewpoint, houseboat life on the lakes and local crafts such as pashmina and papier-mâché.'),
    ),

    'best_time' => array(
        array('March – May', 'Spring', 'Blossom season; Srinagar’s tulip garden usually opens around late March to April. Pleasant days, cool nights.'),
        array('June – August', 'Summer', 'Peak season with long, mild days — best for meadows, pony rides and Sonmarg. Book hotels and houseboats early.'),
        array('September – November', 'Autumn', 'Clear skies and chinar trees turning red and gold. Quieter than summer and good for photography.'),
        array('December – February', 'Winter', 'Snow in Gulmarg and Pahalgam, and skiing in Gulmarg. Very cold; heavy snow can close some roads, including towards Sonmarg.'),
    ),
    'best_time_answer' => 'For sightseeing, March to October is the most comfortable time to visit Kashmir; choose December to February if you want snow.',

    'days_answer' => 'Five days is enough for a first Kashmir trip covering Srinagar, Gulmarg and Pahalgam; allow 6–7 days to add Sonmarg, and 8 days if you also want to visit Mata Vaishno Devi at Katra.',
    'days_rows' => array(
        array('4 days', 'Srinagar and Gulmarg', 'srinagar-gulmarg-tour-03nt04dy'),
        array('5 days', 'Srinagar, Gulmarg and Pahalgam, with a houseboat night', 'srinagar-gulmarg-pahalgam-tour-package-5-days'),
        array('5 days', 'Srinagar with day trips to Gulmarg and Sonmarg', 'srinagar-gulmarg-sonmarg-day-trip-tour-package-5-days'),
        array('6 days', 'Srinagar, Gulmarg, Pahalgam and Sonmarg', 'srinagar-pahalgam-gulmarg-sonmarg-package-6-days'),
        array('8 days', 'Kashmir valley plus Katra and Mata Vaishno Devi, ending in Jammu', 'srinagar-gulmarg-sonmarg-pahalgam-katra-tour-package-8-days'),
    ),

    'cost_answer' => 'The cost of a Kashmir tour depends mainly on the number of nights, hotel category, travel season and group size. We quote each trip for your dates; air or train fare to Srinagar is usually extra.',
    'cost_factors' => array(
        'Hotel category and whether you add a houseboat night',
        'Season — summer and holiday weeks cost more than shoulder months',
        'Number of travellers sharing a room and a private cab',
        'Meal plan (most of our Kashmir packages include breakfast and dinner)',
        'Optional activities paid locally, such as the Gulmarg Gondola, shikara extensions or pony rides',
        'Flights or trains to Srinagar, which are not included unless stated',
        'GST, shown separately where a package lists it as extra',
    ),

    'places' => array(
        array('Srinagar', 'The base for most trips: shikara rides on Dal Lake, houseboat stays, and the Mughal gardens — Nishat Bagh, Shalimar Bagh, Chashme Shahi and Pari Mahal — plus the Shankaracharya temple viewpoint.'),
        array('Gulmarg', 'About 56 km from Srinagar. Meadows, snow views and the Gulmarg Gondola cable car (tickets at your own cost); known for skiing in winter.'),
        array('Pahalgam', 'In the Lidder valley, roughly a 186 km round trip from Srinagar. Riverside walks and local-transport excursions to nearby valleys.'),
        array('Sonmarg', 'The “meadow of gold”, roughly a 196 km round trip from Srinagar. Ponies go up towards the Thajiwas glacier, where snow can remain into summer.'),
        array('Katra', 'About 268 km (6–7 hours) from Srinagar. Base for the 13 km trek to Mata Vaishno Devi, with pony and palki options.'),
    ),

    'things' => array(
        'Take a shikara ride on Dal Lake and spend a night on a houseboat',
        'Walk the terraced Mughal gardens in Srinagar',
        'Ride the Gulmarg Gondola for high-altitude views (own cost)',
        'Take a pony ride towards the Thajiwas glacier at Sonmarg',
        'Explore the Lidder valley around Pahalgam',
        'Shop for pashmina, walnut-wood and papier-mâché crafts in Srinagar',
    ),

    'stay' => 'Our Kashmir packages use hotels in Srinagar, Gulmarg or Pahalgam — deluxe category on twin sharing in most itineraries — and many include one night on a Dal Lake houseboat. If a listed hotel is unavailable, a hotel of similar standard is arranged.',
    'transport' => 'Srinagar airport (SXR) has direct flights from Delhi and other major Indian cities. Our packages start with pick-up at Srinagar and use a private cab for all transfers and sightseeing in the itinerary. Some local excursions — for example ponies at Sonmarg or the Gondola at Gulmarg — are paid locally.',
    'family' => 'Kashmir suits families well: most sightseeing is by road with short walks, and ponies or cable cars reach the higher points. Most of our Kashmir packages list children below 5 as complimentary, with extra child and extra adult rates in each package’s terms.',
    'honeymoon' => 'For couples, a houseboat night on Dal Lake, an evening shikara ride and quieter autumn dates work well. Ask us to upgrade hotels or add a night in Gulmarg or Pahalgam.',
    'adventure' => 'Skiing in Gulmarg in winter, the Gondola to higher altitudes, pony rides in Sonmarg and Pahalgam, and short hikes around the meadows.',

    'tips' => array(
        'Carry a government photo ID for hotel check-ins; foreign nationals need a passport and valid Indian visa.',
        'Prepaid mobile SIMs issued outside Jammu & Kashmir generally do not work there; postpaid connections usually do.',
        'Pack layers even in summer — mornings and evenings are cool, and it can be much colder in Gulmarg and Sonmarg.',
        'Book early for June–August, long weekends and the Amarnath Yatra period, when hotels fill fast.',
        'Snow and weather can change sightseeing plans at short notice; the driver will follow what is open that day.',
    ),

    'faqs' => array(
        array('What is included in Kashmir tour packages?', '<p>Most of our Kashmir packages include hotel stays (deluxe category on twin sharing), a houseboat night in several itineraries, breakfast and dinner, a private cab for transfers and sightseeing, tolls, parking and driver allowance. Each package page lists its exact inclusions and exclusions.</p>'),
        array('How many days are enough for Kashmir?', '<p>Five days covers Srinagar, Gulmarg and Pahalgam. Choose 6–7 days to add Sonmarg, or 8 days to include Katra and Mata Vaishno Devi.</p>'),
        array('What is the best time to visit Kashmir?', '<p>March to October for sightseeing and meadows; December to February for snow and skiing in Gulmarg.</p>'),
        array('Which places are usually covered?', '<p>Srinagar, Gulmarg and Pahalgam in almost every package; Sonmarg and Katra in the longer itineraries.</p>'),
        array('Are flights included?', '<p>No — our Kashmir packages start and end at Srinagar (one ends in Jammu). We can add flights or trains to your quote on request.</p>'),
        array('Can Kashmir packages be customized?', '<p>Yes. You can change hotels, add a houseboat or extra nights, or combine destinations. <a href="/customized-holidays?destination=Kashmir">Tell us what you need</a>.</p>'),
    ),
);
