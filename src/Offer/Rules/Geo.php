<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 8/15/2017
 * Time: 5:11 PM
 */

namespace LeadMax\TrackYourStats\Offer\Rules;


use GeoIp2\Database\Reader;
use GeoIp2\ProviderInterface;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class Geo implements Rule
{

    // countries list from github
    static $countries = array(
        "AF" => "Afghanistan",
        "AL" => "Albania",
        "DZ" => "Algeria",
        "AS" => "American Samoa",
        "AD" => "Andorra",
        "AO" => "Angola",
        "AI" => "Anguilla",
        "AQ" => "Antarctica",
        "AG" => "Antigua and Barbuda",
        "AR" => "Argentina",
        "AM" => "Armenia",
        "AW" => "Aruba",
        "AU" => "Australia",
        "AT" => "Austria",
        "AZ" => "Azerbaijan",
        "BS" => "Bahamas",
        "BH" => "Bahrain",
        "BD" => "Bangladesh",
        "BB" => "Barbados",
        "BY" => "Belarus",
        "BE" => "Belgium",
        "BZ" => "Belize",
        "BJ" => "Benin",
        "BM" => "Bermuda",
        "BT" => "Bhutan",
        "BO" => "Bolivia",
        "BA" => "Bosnia and Herzegovina",
        "BW" => "Botswana",
        "BV" => "Bouvet Island",
        "BR" => "Brazil",
        "IO" => "British Indian Ocean Territory",
        "BN" => "Brunei Darussalam",
        "BG" => "Bulgaria",
        "BF" => "Burkina Faso",
        "BI" => "Burundi",
        "KH" => "Cambodia",
        "CM" => "Cameroon",
        "CA" => "Canada",
        "CV" => "Cape Verde",
        "KY" => "Cayman Islands",
        "CF" => "Central African Republic",
        "TD" => "Chad",
        "CL" => "Chile",
        "CN" => "China",
        "CX" => "Christmas Island",
        "CC" => "Cocos (Keeling) Islands",
        "CO" => "Colombia",
        "KM" => "Comoros",
        "CG" => "Congo",
        "CD" => "Congo, the Democratic Republic of the",
        "CK" => "Cook Islands",
        "CR" => "Costa Rica",
        "CI" => "Cote D'Ivoire",
        "HR" => "Croatia",
        "CU" => "Cuba",
        "CY" => "Cyprus",
        "CZ" => "Czech Republic",
        "DK" => "Denmark",
        "DJ" => "Djibouti",
        "DM" => "Dominica",
        "DO" => "Dominican Republic",
        "EC" => "Ecuador",
        "EG" => "Egypt",
        "SV" => "El Salvador",
        "GQ" => "Equatorial Guinea",
        "ER" => "Eritrea",
        "EE" => "Estonia",
        "ET" => "Ethiopia",
        "FK" => "Falkland Islands (Malvinas)",
        "FO" => "Faroe Islands",
        "FJ" => "Fiji",
        "FI" => "Finland",
        "FR" => "France",
        "GF" => "French Guiana",
        "PF" => "French Polynesia",
        "TF" => "French Southern Territories",
        "GA" => "Gabon",
        "GM" => "Gambia",
        "GE" => "Georgia",
        "DE" => "Germany",
        "GH" => "Ghana",
        "GI" => "Gibraltar",
        "GR" => "Greece",
        "GL" => "Greenland",
        "GD" => "Grenada",
        "GP" => "Guadeloupe",
        "GU" => "Guam",
        "GT" => "Guatemala",
        "GN" => "Guinea",
        "GW" => "Guinea-Bissau",
        "GY" => "Guyana",
        "HT" => "Haiti",
        "HM" => "Heard Island and Mcdonald Islands",
        "VA" => "Holy See (Vatican City State)",
        "HN" => "Honduras",
        "HK" => "Hong Kong",
        "HU" => "Hungary",
        "IS" => "Iceland",
        "IN" => "India",
        "ID" => "Indonesia",
        "IR" => "Iran, Islamic Republic of",
        "IQ" => "Iraq",
        "IE" => "Ireland",
        "IL" => "Israel",
        "IT" => "Italy",
        "JM" => "Jamaica",
        "JP" => "Japan",
        "JO" => "Jordan",
        "KZ" => "Kazakhstan",
        "KE" => "Kenya",
        "KI" => "Kiribati",
        "KP" => "Korea, Democratic People's Republic of",
        "KR" => "Korea, Republic of",
        "KW" => "Kuwait",
        "KG" => "Kyrgyzstan",
        "LA" => "Lao People's Democratic Republic",
        "LV" => "Latvia",
        "LB" => "Lebanon",
        "LS" => "Lesotho",
        "LR" => "Liberia",
        "LY" => "Libyan Arab Jamahiriya",
        "LI" => "Liechtenstein",
        "LT" => "Lithuania",
        "LU" => "Luxembourg",
        "MO" => "Macao",
        "MK" => "Macedonia, the Former Yugoslav Republic of",
        "MG" => "Madagascar",
        "MW" => "Malawi",
        "MY" => "Malaysia",
        "MV" => "Maldives",
        "ML" => "Mali",
        "MT" => "Malta",
        "MH" => "Marshall Islands",
        "MQ" => "Martinique",
        "MR" => "Mauritania",
        "MU" => "Mauritius",
        "YT" => "Mayotte",
        "MX" => "Mexico",
        "FM" => "Micronesia, Federated States of",
        "MD" => "Moldova, Republic of",
        "MC" => "Monaco",
        "MN" => "Mongolia",
        "MS" => "Montserrat",
        "MA" => "Morocco",
        "MZ" => "Mozambique",
        "MM" => "Myanmar",
        "NA" => "Namibia",
        "NR" => "Nauru",
        "NP" => "Nepal",
        "NL" => "Netherlands",
        "AN" => "Netherlands Antilles",
        "NC" => "New Caledonia",
        "NZ" => "New Zealand",
        "NI" => "Nicaragua",
        "NE" => "Niger",
        "NG" => "Nigeria",
        "NU" => "Niue",
        "NF" => "Norfolk Island",
        "MP" => "Northern Mariana Islands",
        "NO" => "Norway",
        "OM" => "Oman",
        "PK" => "Pakistan",
        "PW" => "Palau",
        "PS" => "Palestinian Territory, Occupied",
        "PA" => "Panama",
        "PG" => "Papua New Guinea",
        "PY" => "Paraguay",
        "PE" => "Peru",
        "PH" => "Philippines",
        "PN" => "Pitcairn",
        "PL" => "Poland",
        "PT" => "Portugal",
        "PR" => "Puerto Rico",
        "QA" => "Qatar",
        "RE" => "Reunion",
        "RO" => "Romania",
        "RU" => "Russian Federation",
        "RW" => "Rwanda",
        "SH" => "Saint Helena",
        "KN" => "Saint Kitts and Nevis",
        "LC" => "Saint Lucia",
        "PM" => "Saint Pierre and Miquelon",
        "VC" => "Saint Vincent and the Grenadines",
        "WS" => "Samoa",
        "SM" => "San Marino",
        "ST" => "Sao Tome and Principe",
        "SA" => "Saudi Arabia",
        "SN" => "Senegal",
        "CS" => "Serbia and Montenegro",
        "SC" => "Seychelles",
        "SL" => "Sierra Leone",
        "SG" => "Singapore",
        "SK" => "Slovakia",
        "SI" => "Slovenia",
        "SB" => "Solomon Islands",
        "SO" => "Somalia",
        "ZA" => "South Africa",
        "GS" => "South Georgia and the South Sandwich Islands",
        "ES" => "Spain",
        "LK" => "Sri Lanka",
        "SD" => "Sudan",
        "SR" => "Suriname",
        "SJ" => "Svalbard and Jan Mayen",
        "SZ" => "Swaziland",
        "SE" => "Sweden",
        "CH" => "Switzerland",
        "SY" => "Syrian Arab Republic",
        "TW" => "Taiwan, Province of China",
        "TJ" => "Tajikistan",
        "TZ" => "Tanzania, United Republic of",
        "TH" => "Thailand",
        "TL" => "Timor-Leste",
        "TG" => "Togo",
        "TK" => "Tokelau",
        "TO" => "Tonga",
        "TT" => "Trinidad and Tobago",
        "TN" => "Tunisia",
        "TR" => "Turkey",
        "TM" => "Turkmenistan",
        "TC" => "Turks and Caicos Islands",
        "TV" => "Tuvalu",
        "UG" => "Uganda",
        "UA" => "Ukraine",
        "AE" => "United Arab Emirates",
        "GB" => "United Kingdom",
        "US" => "United States",
        "UM" => "United States Minor Outlying Islands",
        "UY" => "Uruguay",
        "UZ" => "Uzbekistan",
        "VU" => "Vanuatu",
        "VE" => "Venezuela",
        "VN" => "Viet Nam",
        "VG" => "Virgin Islands, British",
        "VI" => "Virgin Islands, U.s.",
        "WF" => "Wallis and Futuna",
        "EH" => "Western Sahara",
        "YE" => "Yemen",
        "ZM" => "Zambia",
        "ZW" => "Zimbabwe",
    );


    private $ip;
    private $reader;
    private $filteredRules = [];
    public $redirectOffer = 0;

    public function __construct($rows, $ip = null, ?ProviderInterface $reader = null)
    {
        $this->ip = $ip;
        $this->reader = $reader;
        foreach ($rows as $row) {
            if ($row['type'] !== 'geo' || !$row['is_active']) {
                continue;
            }
            $id = $row['idrule'];
            $this->filteredRules[$id]['rule'] = $row;
            $this->filteredRules[$id]['countries'][$row['country_code'] ?? ''] =
                $row['cap_status'] ? (int) $row['cap'] : null;
        }
        ksort($this->filteredRules, SORT_NUMERIC);
    }

    public function getRedirectOffer()
    {
        return $this->redirectOffer;
    }

    public function checkRules()
    {
        if (!$this->filteredRules) {
            return true;
        }

        $this->reader ??= new Reader(env('GEO_IP_DATABASE'));
        $country = $this->countryForIp($this->ip);

        $conversionCount = null;
        foreach ($this->filteredRules as $entry) {
            $rule = $entry['rule'];
            $matches = array_key_exists($country, $entry['countries']);
            if ($rule['deny'] ? $matches : !$matches) {
                $this->redirectOffer = $rule['redirect_offer'];
                return false;
            }
            $cap = $entry['countries'][$country] ?? null;
            if ($cap !== null) {
                $conversionCount ??= $this->dailyConversions($rule['offer_idoffer'], $country);
                if ($conversionCount >= $cap) {
                    $this->redirectOffer = $rule['redirect_offer'];
                    return false;
                }
            }
        }
        return true;
    }

    private function dailyConversions($offerId, $country): int
    {
        $today = CarbonImmutable::today('America/New_York');
        $conversions = DB::table('conversions')
            ->join('clicks', 'clicks.idclicks', '=', 'conversions.click_id')
            ->where('clicks.offer_idoffer', $offerId)
            ->where('conversions.timestamp', '>=', $today->utc())
            ->where('conversions.timestamp', '<', $today->addDay()->utc())
            ->selectRaw('clicks.country_code, clicks.ip_address, COUNT(*) AS total')
            ->groupBy('clicks.country_code', 'clicks.ip_address')
            ->get();

        $count = 0;
        foreach ($conversions as $group) {
            $code = $group->country_code;
            if (!$code || $code === 'UNKNOWN') {
                $code = $this->countryForIp($group->ip_address);
            }
            if ($code === $country) {
                $count += $group->total;
            }
        }
        return $count;
    }

    private function countryForIp($ip): string
    {
        try {
            return $this->reader->city((string) $ip)->country->isoCode ?: 'UNKNOWN';
        } catch (\Exception $e) {
            return 'UNKNOWN';
        }
    }

//prints all countries as a select box, going to add another to print as table for editing and updating offers..
    static
    function echoCountrySelectBox()
    {

        echo "<select id=\"countries\">";
        foreach (static::$countries as $key => $val) {
            echo "<option value=\"$key\">{$val}</option>";
        }
        echo "</select>";

    }

    static function printCountriesAsTable()
    {

        foreach (static::$countries as $key => $val) {
            echo "<tr id='{$key}'>";
            echo "<td>{$val}</td>";
            echo "<td><a id='_{$key}' onclick='addCountry(\"{$key}\");' href='javascript:void(0);'><img id='{$key}_img' src='images/icons/add.png'></a></td>";
            echo "</tr>";

        }
    }


}