<?php

namespace App\Helpers;

/**
 * Class AustralianSuburbs
 *
 * A utility helper providing localized data for Australian geographic regions
 * and residential classifications, typically used for form population.
 *
 * @package App\Helpers
 */
class AustralianSuburbs
{
    /**
     * Retrieve a list of major suburbs/cities for a specific Australian state.
     *
     * @param string $state The ISO 3166-2:AU state code (e.g., 'NSW', 'VIC').
     * @return array<int, string> A list of suburb names, or an empty array if the state is not found.
     */
    public static function getSuburbsByState(string $state): array
    {
        $suburbs = [
            'NSW' => [
                'Sydney', 'Parramatta', 'Newcastle', 'Wollongong', 'Liverpool',
                'Penrith', 'Blacktown', 'Campbelltown', 'Sutherland', 'Hornsby',
                'Bondi', 'Manly', 'Chatswood', 'Bankstown', 'Canterbury'
            ],
            'VIC' => [
                'Melbourne', 'Geelong', 'Ballarat', 'Bendigo', 'Frankston',
                'Dandenong', 'Casey', 'Monash', 'Whitehorse', 'Kingston',
                'Glen Waverley', 'Ringwood', 'Box Hill', 'St Kilda', 'Richmond'
            ],
            'QLD' => [
                'Brisbane', 'Gold Coast', 'Townsville', 'Cairns', 'Toowoomba',
                'Mackay', 'Rockhampton', 'Bundaberg', 'Hervey Bay', 'Gladstone',
                'Southport', 'Surfers Paradise', 'Ipswich', 'Logan', 'Redland'
            ],
            'SA' => [
                'Adelaide', 'Mount Gambier', 'Gawler', 'Whyalla', 'Murray Bridge',
                'Port Adelaide', 'Port Lincoln', 'Victor Harbor', 'Glenelg', 'Norwood'
            ],
            'WA' => [
                'Perth', 'Mandurah', 'Bunbury', 'Kalgoorlie', 'Geraldton',
                'Albany', 'Fremantle', 'Joondalup', 'Rockingham', 'Armadale'
            ],
            'TAS' => [
                'Hobart', 'Launceston', 'Burnie', 'Devonport', 'Kingston',
                'Glenorchy', 'Clarence', 'Brighton', 'Sorell', 'Huonville'
            ],
            'NT' => [
                'Darwin', 'Alice Springs', 'Palmerston', 'Katherine', 'Nhulunbuy',
                'Tennant Creek', 'Casuarina', 'Howard Springs', 'Jabiru', 'Yulara'
            ],
            'ACT' => [
                'Canberra', 'Belconnen', 'Woden', 'Tuggeranong', 'Gungahlin',
                'Civic', 'Braddon', 'Dickson', 'Kingston', 'Manuka'
            ],
        ];

        return $suburbs[$state] ?? [];
    }

    /**
     * Get a key-value mapping of all Australian States and Territories.
     *
     * @return array<string, string> An associative array where key is the code and value is the full name.
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
     * Get the standardized residential status options for the application workflow.
     *
     * @return array<string, string> Associative array of internal status keys to human-readable labels.
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
