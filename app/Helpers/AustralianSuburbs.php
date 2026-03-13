<?php

namespace App\Helpers;

/**
 * Class AustralianSuburbs
 *
 * Utility helper providing Australian geographic data for form population and API search.
 */
class AustralianSuburbs
{
    /**
     * Full suburb dataset: each entry is [suburb, state, postcode].
     * Used by the suburb search API endpoint.
     */
    public static function getAllSuburbs(): array
    {
        return [
            // NSW
            ['suburb' => 'Sydney',         'state' => 'NSW', 'postcode' => '2000'],
            ['suburb' => 'Parramatta',      'state' => 'NSW', 'postcode' => '2150'],
            ['suburb' => 'Newcastle',       'state' => 'NSW', 'postcode' => '2300'],
            ['suburb' => 'Wollongong',      'state' => 'NSW', 'postcode' => '2500'],
            ['suburb' => 'Liverpool',       'state' => 'NSW', 'postcode' => '2170'],
            ['suburb' => 'Penrith',         'state' => 'NSW', 'postcode' => '2750'],
            ['suburb' => 'Blacktown',       'state' => 'NSW', 'postcode' => '2148'],
            ['suburb' => 'Campbelltown',    'state' => 'NSW', 'postcode' => '2560'],
            ['suburb' => 'Sutherland',      'state' => 'NSW', 'postcode' => '2232'],
            ['suburb' => 'Hornsby',         'state' => 'NSW', 'postcode' => '2077'],
            ['suburb' => 'Bondi',           'state' => 'NSW', 'postcode' => '2026'],
            ['suburb' => 'Manly',           'state' => 'NSW', 'postcode' => '2095'],
            ['suburb' => 'Chatswood',       'state' => 'NSW', 'postcode' => '2067'],
            ['suburb' => 'Bankstown',       'state' => 'NSW', 'postcode' => '2200'],
            ['suburb' => 'Canterbury',      'state' => 'NSW', 'postcode' => '2193'],
            ['suburb' => 'Burwood',         'state' => 'NSW', 'postcode' => '2134'],
            ['suburb' => 'Strathfield',     'state' => 'NSW', 'postcode' => '2135'],
            ['suburb' => 'Hurstville',      'state' => 'NSW', 'postcode' => '2220'],
            ['suburb' => 'Kogarah',         'state' => 'NSW', 'postcode' => '2217'],
            ['suburb' => 'Rockdale',        'state' => 'NSW', 'postcode' => '2216'],
            ['suburb' => 'Marrickville',    'state' => 'NSW', 'postcode' => '2204'],
            ['suburb' => 'Leichhardt',      'state' => 'NSW', 'postcode' => '2040'],
            ['suburb' => 'Newtown',         'state' => 'NSW', 'postcode' => '2042'],
            ['suburb' => 'Surry Hills',     'state' => 'NSW', 'postcode' => '2010'],
            ['suburb' => 'Redfern',         'state' => 'NSW', 'postcode' => '2016'],
            ['suburb' => 'Ultimo',          'state' => 'NSW', 'postcode' => '2007'],
            ['suburb' => 'Pyrmont',         'state' => 'NSW', 'postcode' => '2009'],
            ['suburb' => 'Rozelle',         'state' => 'NSW', 'postcode' => '2039'],
            ['suburb' => 'Balmain',         'state' => 'NSW', 'postcode' => '2041'],
            ['suburb' => 'Mosman',          'state' => 'NSW', 'postcode' => '2088'],
            ['suburb' => 'Neutral Bay',     'state' => 'NSW', 'postcode' => '2089'],
            ['suburb' => 'North Sydney',    'state' => 'NSW', 'postcode' => '2060'],
            ['suburb' => 'St Leonards',     'state' => 'NSW', 'postcode' => '2065'],
            ['suburb' => 'Lane Cove',       'state' => 'NSW', 'postcode' => '2066'],
            ['suburb' => 'Ryde',            'state' => 'NSW', 'postcode' => '2112'],
            ['suburb' => 'Epping',          'state' => 'NSW', 'postcode' => '2121'],
            ['suburb' => 'Carlingford',     'state' => 'NSW', 'postcode' => '2118'],
            ['suburb' => 'Castle Hill',     'state' => 'NSW', 'postcode' => '2154'],
            ['suburb' => 'Baulkham Hills',  'state' => 'NSW', 'postcode' => '2153'],
            ['suburb' => 'Kellyville',      'state' => 'NSW', 'postcode' => '2155'],
            ['suburb' => 'Windsor',         'state' => 'NSW', 'postcode' => '2756'],
            ['suburb' => 'Richmond',        'state' => 'NSW', 'postcode' => '2753'],
            ['suburb' => 'Katoomba',        'state' => 'NSW', 'postcode' => '2780'],
            ['suburb' => 'Gosford',         'state' => 'NSW', 'postcode' => '2250'],
            ['suburb' => 'Wyong',           'state' => 'NSW', 'postcode' => '2259'],
            ['suburb' => 'Tuggerah',        'state' => 'NSW', 'postcode' => '2259'],
            ['suburb' => 'Bathurst',        'state' => 'NSW', 'postcode' => '2795'],
            ['suburb' => 'Orange',          'state' => 'NSW', 'postcode' => '2800'],
            ['suburb' => 'Dubbo',           'state' => 'NSW', 'postcode' => '2830'],
            ['suburb' => 'Tamworth',        'state' => 'NSW', 'postcode' => '2340'],
            ['suburb' => 'Albury',          'state' => 'NSW', 'postcode' => '2640'],
            ['suburb' => 'Wagga Wagga',     'state' => 'NSW', 'postcode' => '2650'],
            ['suburb' => 'Lismore',         'state' => 'NSW', 'postcode' => '2480'],
            ['suburb' => 'Coffs Harbour',   'state' => 'NSW', 'postcode' => '2450'],
            ['suburb' => 'Port Macquarie',  'state' => 'NSW', 'postcode' => '2444'],
            ['suburb' => 'Armidale',        'state' => 'NSW', 'postcode' => '2350'],
            ['suburb' => 'Broken Hill',     'state' => 'NSW', 'postcode' => '2880'],
            ['suburb' => 'Byron Bay',       'state' => 'NSW', 'postcode' => '2481'],
            ['suburb' => 'Ballina',         'state' => 'NSW', 'postcode' => '2478'],
            ['suburb' => 'Grafton',         'state' => 'NSW', 'postcode' => '2460'],
            // VIC
            ['suburb' => 'Melbourne',       'state' => 'VIC', 'postcode' => '3000'],
            ['suburb' => 'Geelong',         'state' => 'VIC', 'postcode' => '3220'],
            ['suburb' => 'Ballarat',        'state' => 'VIC', 'postcode' => '3350'],
            ['suburb' => 'Bendigo',         'state' => 'VIC', 'postcode' => '3550'],
            ['suburb' => 'Frankston',       'state' => 'VIC', 'postcode' => '3199'],
            ['suburb' => 'Dandenong',       'state' => 'VIC', 'postcode' => '3175'],
            ['suburb' => 'Glen Waverley',   'state' => 'VIC', 'postcode' => '3150'],
            ['suburb' => 'Ringwood',        'state' => 'VIC', 'postcode' => '3134'],
            ['suburb' => 'Box Hill',        'state' => 'VIC', 'postcode' => '3128'],
            ['suburb' => 'St Kilda',        'state' => 'VIC', 'postcode' => '3182'],
            ['suburb' => 'Richmond',        'state' => 'VIC', 'postcode' => '3121'],
            ['suburb' => 'Fitzroy',         'state' => 'VIC', 'postcode' => '3065'],
            ['suburb' => 'Carlton',         'state' => 'VIC', 'postcode' => '3053'],
            ['suburb' => 'Collingwood',     'state' => 'VIC', 'postcode' => '3066'],
            ['suburb' => 'Prahran',         'state' => 'VIC', 'postcode' => '3181'],
            ['suburb' => 'South Yarra',     'state' => 'VIC', 'postcode' => '3141'],
            ['suburb' => 'Toorak',          'state' => 'VIC', 'postcode' => '3142'],
            ['suburb' => 'Hawthorn',        'state' => 'VIC', 'postcode' => '3122'],
            ['suburb' => 'Camberwell',      'state' => 'VIC', 'postcode' => '3124'],
            ['suburb' => 'Malvern',         'state' => 'VIC', 'postcode' => '3144'],
            ['suburb' => 'Caulfield',       'state' => 'VIC', 'postcode' => '3162'],
            ['suburb' => 'Brighton',        'state' => 'VIC', 'postcode' => '3186'],
            ['suburb' => 'Sandringham',     'state' => 'VIC', 'postcode' => '3191'],
            ['suburb' => 'Moorabbin',       'state' => 'VIC', 'postcode' => '3189'],
            ['suburb' => 'Springvale',      'state' => 'VIC', 'postcode' => '3171'],
            ['suburb' => 'Noble Park',      'state' => 'VIC', 'postcode' => '3174'],
            ['suburb' => 'Cranbourne',      'state' => 'VIC', 'postcode' => '3977'],
            ['suburb' => 'Berwick',         'state' => 'VIC', 'postcode' => '3806'],
            ['suburb' => 'Pakenham',        'state' => 'VIC', 'postcode' => '3810'],
            ['suburb' => 'Narre Warren',    'state' => 'VIC', 'postcode' => '3805'],
            ['suburb' => 'Werribee',        'state' => 'VIC', 'postcode' => '3030'],
            ['suburb' => 'Hoppers Crossing','state' => 'VIC', 'postcode' => '3029'],
            ['suburb' => 'Point Cook',      'state' => 'VIC', 'postcode' => '3030'],
            ['suburb' => 'Sunbury',         'state' => 'VIC', 'postcode' => '3429'],
            ['suburb' => 'Melton',          'state' => 'VIC', 'postcode' => '3337'],
            ['suburb' => 'Broadmeadows',    'state' => 'VIC', 'postcode' => '3047'],
            ['suburb' => 'Craigieburn',     'state' => 'VIC', 'postcode' => '3064'],
            ['suburb' => 'Epping',          'state' => 'VIC', 'postcode' => '3076'],
            ['suburb' => 'South Melbourne', 'state' => 'VIC', 'postcode' => '3205'],
            ['suburb' => 'Port Melbourne',  'state' => 'VIC', 'postcode' => '3207'],
            ['suburb' => 'Docklands',       'state' => 'VIC', 'postcode' => '3008'],
            ['suburb' => 'Mildura',         'state' => 'VIC', 'postcode' => '3500'],
            ['suburb' => 'Shepparton',      'state' => 'VIC', 'postcode' => '3630'],
            ['suburb' => 'Wodonga',         'state' => 'VIC', 'postcode' => '3690'],
            ['suburb' => 'Warrnambool',     'state' => 'VIC', 'postcode' => '3280'],
            ['suburb' => 'Traralgon',       'state' => 'VIC', 'postcode' => '3844'],
            // QLD
            ['suburb' => 'Brisbane',        'state' => 'QLD', 'postcode' => '4000'],
            ['suburb' => 'Gold Coast',      'state' => 'QLD', 'postcode' => '4217'],
            ['suburb' => 'Townsville',      'state' => 'QLD', 'postcode' => '4810'],
            ['suburb' => 'Cairns',          'state' => 'QLD', 'postcode' => '4870'],
            ['suburb' => 'Toowoomba',       'state' => 'QLD', 'postcode' => '4350'],
            ['suburb' => 'Mackay',          'state' => 'QLD', 'postcode' => '4740'],
            ['suburb' => 'Rockhampton',     'state' => 'QLD', 'postcode' => '4700'],
            ['suburb' => 'Bundaberg',       'state' => 'QLD', 'postcode' => '4670'],
            ['suburb' => 'Hervey Bay',      'state' => 'QLD', 'postcode' => '4655'],
            ['suburb' => 'Gladstone',       'state' => 'QLD', 'postcode' => '4680'],
            ['suburb' => 'Southport',       'state' => 'QLD', 'postcode' => '4215'],
            ['suburb' => 'Surfers Paradise','state' => 'QLD', 'postcode' => '4217'],
            ['suburb' => 'Ipswich',         'state' => 'QLD', 'postcode' => '4305'],
            ['suburb' => 'Logan',           'state' => 'QLD', 'postcode' => '4114'],
            ['suburb' => 'Redland Bay',     'state' => 'QLD', 'postcode' => '4165'],
            ['suburb' => 'Springwood',      'state' => 'QLD', 'postcode' => '4127'],
            ['suburb' => 'Beenleigh',       'state' => 'QLD', 'postcode' => '4207'],
            ['suburb' => 'Robina',          'state' => 'QLD', 'postcode' => '4226'],
            ['suburb' => 'Broadbeach',      'state' => 'QLD', 'postcode' => '4218'],
            ['suburb' => 'Burleigh Heads',  'state' => 'QLD', 'postcode' => '4220'],
            ['suburb' => 'Coolangatta',     'state' => 'QLD', 'postcode' => '4225'],
            ['suburb' => 'Chermside',       'state' => 'QLD', 'postcode' => '4032'],
            ['suburb' => 'Nundah',          'state' => 'QLD', 'postcode' => '4012'],
            ['suburb' => 'Woolloongabba',   'state' => 'QLD', 'postcode' => '4102'],
            ['suburb' => 'South Brisbane',  'state' => 'QLD', 'postcode' => '4101'],
            ['suburb' => 'West End',        'state' => 'QLD', 'postcode' => '4101'],
            ['suburb' => 'Fortitude Valley','state' => 'QLD', 'postcode' => '4006'],
            ['suburb' => 'New Farm',        'state' => 'QLD', 'postcode' => '4005'],
            ['suburb' => 'Paddington',      'state' => 'QLD', 'postcode' => '4064'],
            ['suburb' => 'Sunnybank',       'state' => 'QLD', 'postcode' => '4109'],
            ['suburb' => 'Carindale',       'state' => 'QLD', 'postcode' => '4152'],
            ['suburb' => 'Capalaba',        'state' => 'QLD', 'postcode' => '4157'],
            ['suburb' => 'Wynnum',          'state' => 'QLD', 'postcode' => '4178'],
            ['suburb' => 'Strathpine',      'state' => 'QLD', 'postcode' => '4500'],
            ['suburb' => 'Caboolture',      'state' => 'QLD', 'postcode' => '4510'],
            ['suburb' => 'Noosa',           'state' => 'QLD', 'postcode' => '4567'],
            ['suburb' => 'Maroochydore',    'state' => 'QLD', 'postcode' => '4558'],
            ['suburb' => 'Caloundra',       'state' => 'QLD', 'postcode' => '4551'],
            // SA
            ['suburb' => 'Adelaide',        'state' => 'SA', 'postcode' => '5000'],
            ['suburb' => 'Glenelg',         'state' => 'SA', 'postcode' => '5045'],
            ['suburb' => 'Norwood',         'state' => 'SA', 'postcode' => '5067'],
            ['suburb' => 'Port Adelaide',   'state' => 'SA', 'postcode' => '5015'],
            ['suburb' => 'Mount Barker',    'state' => 'SA', 'postcode' => '5251'],
            ['suburb' => 'Murray Bridge',   'state' => 'SA', 'postcode' => '5253'],
            ['suburb' => 'Mount Gambier',   'state' => 'SA', 'postcode' => '5290'],
            ['suburb' => 'Whyalla',         'state' => 'SA', 'postcode' => '5600'],
            ['suburb' => 'Port Lincoln',    'state' => 'SA', 'postcode' => '5606'],
            ['suburb' => 'Victor Harbor',   'state' => 'SA', 'postcode' => '5211'],
            ['suburb' => 'Gawler',          'state' => 'SA', 'postcode' => '5118'],
            ['suburb' => 'Salisbury',       'state' => 'SA', 'postcode' => '5108'],
            ['suburb' => 'Elizabeth',       'state' => 'SA', 'postcode' => '5112'],
            ['suburb' => 'Modbury',         'state' => 'SA', 'postcode' => '5092'],
            ['suburb' => 'Tea Tree Gully',  'state' => 'SA', 'postcode' => '5091'],
            ['suburb' => 'Marion',          'state' => 'SA', 'postcode' => '5043'],
            ['suburb' => 'Morphett Vale',   'state' => 'SA', 'postcode' => '5162'],
            ['suburb' => 'Noarlunga',       'state' => 'SA', 'postcode' => '5168'],
            ['suburb' => 'Mawson Lakes',    'state' => 'SA', 'postcode' => '5095'],
            ['suburb' => 'Unley',           'state' => 'SA', 'postcode' => '5061'],
            // WA
            ['suburb' => 'Perth',           'state' => 'WA', 'postcode' => '6000'],
            ['suburb' => 'Fremantle',       'state' => 'WA', 'postcode' => '6160'],
            ['suburb' => 'Joondalup',       'state' => 'WA', 'postcode' => '6027'],
            ['suburb' => 'Rockingham',      'state' => 'WA', 'postcode' => '6168'],
            ['suburb' => 'Armadale',        'state' => 'WA', 'postcode' => '6112'],
            ['suburb' => 'Mandurah',        'state' => 'WA', 'postcode' => '6210'],
            ['suburb' => 'Bunbury',         'state' => 'WA', 'postcode' => '6230'],
            ['suburb' => 'Geraldton',       'state' => 'WA', 'postcode' => '6530'],
            ['suburb' => 'Kalgoorlie',      'state' => 'WA', 'postcode' => '6430'],
            ['suburb' => 'Albany',          'state' => 'WA', 'postcode' => '6330'],
            ['suburb' => 'Midland',         'state' => 'WA', 'postcode' => '6056'],
            ['suburb' => 'Cannington',      'state' => 'WA', 'postcode' => '6107'],
            ['suburb' => 'Victoria Park',   'state' => 'WA', 'postcode' => '6100'],
            ['suburb' => 'Subiaco',         'state' => 'WA', 'postcode' => '6008'],
            ['suburb' => 'Cottesloe',       'state' => 'WA', 'postcode' => '6011'],
            ['suburb' => 'Claremont',       'state' => 'WA', 'postcode' => '6010'],
            ['suburb' => 'Nedlands',        'state' => 'WA', 'postcode' => '6009'],
            ['suburb' => 'Floreat',         'state' => 'WA', 'postcode' => '6014'],
            ['suburb' => 'Scarborough',     'state' => 'WA', 'postcode' => '6019'],
            ['suburb' => 'Balga',           'state' => 'WA', 'postcode' => '6061'],
            ['suburb' => 'Stirling',        'state' => 'WA', 'postcode' => '6021'],
            ['suburb' => 'Karratha',        'state' => 'WA', 'postcode' => '6714'],
            ['suburb' => 'Port Hedland',    'state' => 'WA', 'postcode' => '6721'],
            ['suburb' => 'Broome',          'state' => 'WA', 'postcode' => '6725'],
            // TAS
            ['suburb' => 'Hobart',          'state' => 'TAS', 'postcode' => '7000'],
            ['suburb' => 'Launceston',      'state' => 'TAS', 'postcode' => '7250'],
            ['suburb' => 'Burnie',          'state' => 'TAS', 'postcode' => '7320'],
            ['suburb' => 'Devonport',       'state' => 'TAS', 'postcode' => '7310'],
            ['suburb' => 'Glenorchy',       'state' => 'TAS', 'postcode' => '7010'],
            ['suburb' => 'Clarence',        'state' => 'TAS', 'postcode' => '7018'],
            ['suburb' => 'Kingston',        'state' => 'TAS', 'postcode' => '7050'],
            ['suburb' => 'Brighton',        'state' => 'TAS', 'postcode' => '7030'],
            ['suburb' => 'Sorell',          'state' => 'TAS', 'postcode' => '7172'],
            ['suburb' => 'Huonville',       'state' => 'TAS', 'postcode' => '7109'],
            ['suburb' => 'New Norfolk',     'state' => 'TAS', 'postcode' => '7140'],
            ['suburb' => 'Ulverstone',      'state' => 'TAS', 'postcode' => '7315'],
            // NT
            ['suburb' => 'Darwin',          'state' => 'NT', 'postcode' => '0800'],
            ['suburb' => 'Alice Springs',   'state' => 'NT', 'postcode' => '0870'],
            ['suburb' => 'Palmerston',      'state' => 'NT', 'postcode' => '0830'],
            ['suburb' => 'Katherine',       'state' => 'NT', 'postcode' => '0850'],
            ['suburb' => 'Nhulunbuy',       'state' => 'NT', 'postcode' => '0881'],
            ['suburb' => 'Tennant Creek',   'state' => 'NT', 'postcode' => '0860'],
            ['suburb' => 'Casuarina',       'state' => 'NT', 'postcode' => '0810'],
            ['suburb' => 'Howard Springs',  'state' => 'NT', 'postcode' => '0835'],
            ['suburb' => 'Jabiru',          'state' => 'NT', 'postcode' => '0886'],
            ['suburb' => 'Yulara',          'state' => 'NT', 'postcode' => '0872'],
            // ACT
            ['suburb' => 'Canberra',        'state' => 'ACT', 'postcode' => '2601'],
            ['suburb' => 'Belconnen',       'state' => 'ACT', 'postcode' => '2617'],
            ['suburb' => 'Woden',           'state' => 'ACT', 'postcode' => '2606'],
            ['suburb' => 'Tuggeranong',     'state' => 'ACT', 'postcode' => '2900'],
            ['suburb' => 'Gungahlin',       'state' => 'ACT', 'postcode' => '2912'],
            ['suburb' => 'Civic',           'state' => 'ACT', 'postcode' => '2601'],
            ['suburb' => 'Braddon',         'state' => 'ACT', 'postcode' => '2612'],
            ['suburb' => 'Dickson',         'state' => 'ACT', 'postcode' => '2602'],
            ['suburb' => 'Kingston',        'state' => 'ACT', 'postcode' => '2604'],
            ['suburb' => 'Manuka',          'state' => 'ACT', 'postcode' => '2603'],
            ['suburb' => 'Queanbeyan',      'state' => 'ACT', 'postcode' => '2620'],
            ['suburb' => 'Fyshwick',        'state' => 'ACT', 'postcode' => '2609'],
        ];
    }

    /**
     * Search suburbs by query string (case-insensitive prefix/contains match).
     * Returns up to $limit results.
     */
    public static function search(string $query, int $limit = 10): array
    {
        $query   = strtolower(trim($query));
        $results = [];

        foreach (self::getAllSuburbs() as $entry) {
            if (str_starts_with(strtolower($entry['suburb']), $query)) {
                $results[] = $entry;
                if (count($results) >= $limit) break;
            }
        }

        // If prefix matches are fewer than limit, add contains matches
        if (count($results) < $limit) {
            foreach (self::getAllSuburbs() as $entry) {
                if (!str_starts_with(strtolower($entry['suburb']), $query)
                    && str_contains(strtolower($entry['suburb']), $query)) {
                    $results[] = $entry;
                    if (count($results) >= $limit) break;
                }
            }
        }

        return $results;
    }

    /**
     * Get a key-value mapping of all Australian States and Territories.
     */
    public static function getAllStates(): array
    {
        return [
            'NSW' => 'New South Wales',
            'VIC' => 'Victoria',
            'QLD' => 'Queensland',
            'SA'  => 'South Australia',
            'WA'  => 'Western Australia',
            'TAS' => 'Tasmania',
            'NT'  => 'Northern Territory',
            'ACT' => 'Australian Capital Territory',
        ];
    }

    /**
     * Get suburbs grouped by state (legacy — kept for any existing callers).
     */
    public static function getSuburbsByState(string $state): array
    {
        return array_values(array_map(
            fn($e) => $e['suburb'],
            array_filter(self::getAllSuburbs(), fn($e) => $e['state'] === $state)
        ));
    }

    /**
     * Residential status options.
     */
    public static function getResidentialStatuses(): array
    {
        return [
            'owner_no_mortgage'   => 'Owner (No Mortgage)',
            'owner_with_mortgage' => 'Owner (With Mortgage)',
            'renting'             => 'Renting',
            'boarding'            => 'Boarding',
            'living_with_parents' => 'Living with Parents/Relatives',
            'other'               => 'Other',
        ];
    }
}