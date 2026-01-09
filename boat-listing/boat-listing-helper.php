<?php

class Boat_Listing_Helper{

    private function cread()
    {
        $cread = ['api_key' => get_option('bl_api_key'),];

        return $cread;
    }

    /**
     * Fetch Boat ids
     */
    public function fetch_boat_ids( $id = null )
    {
        global $wpdb;
        $table = $wpdb->prefix . 'boats';

        // If a specific ID is requested
        if ( $id !== null ) {
            $result = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table WHERE id = %d", $id ) );
            return $result ? intval($result) : null;
        }

        // Otherwise, return all IDs
        $results = $wpdb->get_col( "SELECT id FROM $table" );

        return $results;
    }

    /**
     * Fetch All boats
     */
    public function fetch_all_boats( $id = null ) {

        global $wpdb;
        $table = $wpdb->prefix . 'boats';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['data'] = json_decode($row['data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize model_data for all rows
        foreach ($results as &$row) {
            $row['data'] = json_decode($row['data'], true);
        }

        return $results;
    }

    public function getAvailableYachts()
    {
        global $wpdb;

        $availability_table = $wpdb->prefix . 'yacht_availability';
        $boats_table        = $wpdb->prefix . 'boats';

        // Get all free yachts with boat details
        $results = $wpdb->get_results("
         SELECT 
            b.data,
            ya.yacht_id,
            ya.year AS availability_year,
            ya.availability_string,
            ya.updated_at
        FROM $availability_table AS ya
        INNER JOIN $boats_table AS b
            ON ya.yacht_id = b.id
    ", ARRAY_A);

        return $results;
    }

    /**
     * Fetch boat price list
     */
    public function fetch_price_list( $id = null ) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_price_list';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['price_data'] = json_decode($row['price_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // json decode
        foreach ($results as &$row) {
            $row['price_data'] = json_decode($row['price_data'], true);
        }

        return $results;
    }

    public function fetch_price_list_yatch_id($yacht_id) {
        $lists = $this->fetch_price_list(); // get all price lists

        foreach ($lists as $list) {
            if (!empty($list['price_data']['rows'])) {
                foreach ($list['price_data']['rows'] as $row) {
                    if (isset($row['yachtId']) && $row['yachtId'] == $yacht_id) {
                        return [
                            'currency' => $list['price_data']['currency'] ?? '',
                            'prices'   => $row['prices'] ?? [],
                            'columns'  => $list['price_data']['columns'] ?? []
                        ];
                    }
                }
            }
        }
        return null;
    }

    /**
     * Fetch charter type
     */
    public function fetch_charter_type( $id = null ) {

        global $wpdb;
        $table = $wpdb->prefix . 'boats';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['data'] = json_decode($row['data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize model_data for all rows
        foreach ($results as &$row) {
            $row['data'] = json_decode($row['data'], true);
        }

        return $results;
    }

    /**
     * Fetch Yacht Categories
     */
    public function fetch_category( $id = null ) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_category';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['category_data'] = json_decode($row['category_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize model_data for all rows
        foreach ($results as &$row) {
            $row['category_data'] = json_decode($row['category_data'], true);
        }

        return $results;
    }

    /**
     * Fetch Yacht Charterbase ==================================
     */
    public function fetch_charterbase( $id = null ){

        global $wpdb;
        $table = $wpdb->prefix . 'boat_charterbaese';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['base_data'] = json_decode($row['base_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize data for all rows
        foreach ($results as &$row) {
            $row['base_data'] = json_decode($row['base_data'], true);
        }

        return $results;
    }

    public function fetch_all_yacht_by_company(){

        $companies = $this->fetch_all_companies();
        $all_boats = [];

        foreach ($companies as $company) {

            $company_id = $company['id'];
            $yacht_url = "https://ws.nausys.com/CBMS-external/rest/catalogue/v6/yachts/{$company_id}";

            $ch = curl_init($yacht_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->cread()));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

            $response = curl_exec($ch);
            curl_close($ch);

            $yacht_data = json_decode($response, true);
            if (!empty($yacht_data['yachts'])) {
                $all_boats = array_merge($all_boats, $yacht_data['yachts']);
            }
        }

        return $all_boats;
    }

    // Get all company info
    function fetch_all_companies($id = null) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_companies';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['company_data'] = json_decode($row['company_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize model_data for all rows
        foreach ($results as &$row) {
            $row['company_data'] = json_decode($row['company_data'], true);
        }

        return $results;
    }

    function fetch_country($id = null) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_country';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['country_data'] = json_decode($row['country_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize for all rows
        foreach ($results as &$row) {
            $row['country_data'] = json_decode($row['country_data'], true);
        }

        return $results;
    }

    function fetch_country_state($id = null) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_country_state';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['country_state_data'] = json_decode($row['country_state_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize for all rows
        foreach ($results as &$row) {
            $row['country_state_data'] = json_decode($row['country_state_data'], true);
        }

        // // ✅ Sort alphabetically by English name
        // usort($results, function($a, $b) {
        //     $a_name = $a['location_data']['name']['textEN'] ?? '';
        //     $b_name = $b['location_data']['name']['textEN'] ?? '';
        //     return strcasecmp($a_name, $b_name); // case-insensitive
        // });

        return $results;
    }

    function fetch_regions($id = null) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_regions';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['regions_data'] = json_decode($row['regions_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize for all rows
        foreach ($results as &$row) {
            $row['regions_data'] = json_decode($row['regions_data'], true);
        }

        return $results;
    }

    function fetch_all_locations($id = null) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_locations';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['location_data'] = json_decode($row['location_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize for all rows
        foreach ($results as &$row) {
            $row['location_data'] = json_decode($row['location_data'], true);
        }

        // ✅ Sort alphabetically by English name
        // usort($results, function($a, $b) {
        //     $a_name = $a['location_data']['name']['textEN'] ?? '';
        //     $b_name = $b['location_data']['name']['textEN'] ?? '';
        //     return strcasecmp($a_name, $b_name); // case-insensitive
        // });

        return $results;
    }

    function fetch_all_models($id = null) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_models';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['id']) && $row['id'] == $id) {
                    $row['model_data'] = json_decode($row['model_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize model_data for all rows
        foreach ($results as &$row) {
            $row['model_data'] = json_decode($row['model_data'], true);
        }

        return $results;
    }

    /**
     * Get All free yachts ID
     */
    public function get_all_free_yacht( $from_date = null, $to_date = null ){

        
        $boat = $this->fetch_all_boats();

        $all_yacht_ids = [];

        if ( ! empty( $boat ) ) {
            foreach ( $boat as $b ) {
                if ( isset( $b['id'] ) ) {
                    $all_yacht_ids[] = (int) $b['id'];
                }
            }
        }

            $cread = [
            "credentials" => [
                "username" => get_option('bl_api_key'),
            ],
            "periodFrom" => $from_date,
            "periodTo"   => $to_date,
            "yachts"     => $all_yacht_ids
        ];

        $yacht_url = "https://ws.nausys.com/CBMS-external/rest/yachtReservation/v6/freeYachts";

        $ch = curl_init($yacht_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cread));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        $response = curl_exec($ch);
        curl_close($ch);

        $datas = json_decode($response, true);

        $free_yacht_ids = [];
        // if freeYachts exist
        if (!empty($datas['freeYachts'])) {
            foreach ($datas['freeYachts'] as $data) {
                
                $free_yacht_ids[] = $data['yachtId'];
                
            }
        }

        return $free_yacht_ids;
    }

    /**
     * Get all location ids by region id
     * Return id
     */
    function get_location_ids_by_region_id($region_id) {

        global $wpdb;
        $table = $wpdb->prefix . 'boat_locations';

        $results = $wpdb->get_results("SELECT * FROM $table", ARRAY_A);
        $location_ids = [];

        foreach ($results as $row) {
            $data = json_decode($row['location_data'], true);
            if (!empty($data['regionId']) && $data['regionId'] == $region_id) {
                $location_ids[] = $data['id'];
            }
        }

        return $location_ids;
    }

   function get_region_ids_by_country_id($country_id) {

        $regions = $this->fetch_regions();

        return array_column(array_filter($regions, function($region) use ($country_id) {
            return ($region['regions_data']['countryId'] ?? 0) == $country_id;
        }), 'id');

    }

    // Fetch all boats with pagination and filters ==========
    public function boat_filters($paged = 1, $per_page = 10, $filters = [])
    {
        global $wpdb;

        $boats_table       = $wpdb->prefix . 'boats';
        $availability_table = $wpdb->prefix . 'yacht_availability';
        $charter_base_table = $wpdb->prefix . 'boat_charterbaese';

        // --- 🔥 PRICE-FIRST APPROACH: If date filter exists, fetch prices FIRST ---
        $yacht_ids_with_prices = null;
        $price_data = null;
        //$offer_data = null;

        if (!empty($filters['free_yacht'])) {
            error_log("🎯 Price-First Mode: Fetching prices before boat query...");

            $date_input = $filters['free_yacht'];

            // Parse date range
            if (strpos($date_input, ' to ') !== false) {
                list($start_date_str, $end_date_str) = explode(' to ', $date_input);
                $date_from = date('Y-m-d', strtotime(trim($start_date_str)));
                $date_to = date('Y-m-d', strtotime(trim($end_date_str)));
            } else {
                $date_from = date('Y-m-d', strtotime($date_input));
                $date_to = date('Y-m-d', strtotime($date_input . ' +7 days'));
            }

            $price_start = microtime(true);
            $price_data = $this->get_all_yacht_prices_batch($date_from, $date_to);
           // $offer_data = $this->get_all_yacht_offers_batch($date_from, $date_to);
            $price_time = microtime(true) - $price_start;

            if (!empty($price_data) && is_array($price_data)) {
                $yacht_ids_with_prices = array_keys($price_data);
                error_log("✅ Price API returned " . count($yacht_ids_with_prices) . " yachts with prices in " . round($price_time, 2) . "s");
            } else {
                error_log("⚠️ Price API returned no data - showing all boats without prices");
                $yacht_ids_with_prices = []; // Empty array = no boats will match
            }
        }

        // --- 1️⃣ Get available yachts (filtered by price availability if date filter exists) ---
        $where_conditions = [];

        if ($yacht_ids_with_prices !== null && !empty($yacht_ids_with_prices)) {
            // Only query boats that have prices
            $yacht_ids_str = implode(',', array_map('intval', $yacht_ids_with_prices));
            $where_conditions[] = "ya.yacht_id IN ($yacht_ids_str)";
            error_log("📊 Limiting query to " . count($yacht_ids_with_prices) . " yachts with confirmed prices");
        } elseif ($yacht_ids_with_prices !== null && empty($yacht_ids_with_prices)) {
            // Price filter was applied but NO prices found - return empty result
            error_log("⚠️ No prices available for date range - returning empty result");
            return [
                'boats'     => [],
                'paged'     => $paged,
                'pages'     => 0,
                'per_page'  => $per_page,
                'total'     => 0
            ];
        }

        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

        $rows = $wpdb->get_results("
            SELECT b.data, ya.yacht_id, ya.year AS availability_year, ya.availability_string, ya.updated_at
            FROM $availability_table AS ya
            INNER JOIN $boats_table AS b ON ya.yacht_id = b.id
            $where_clause
            ORDER BY ya.updated_at DESC
        ", ARRAY_A);

        // --- 2️⃣ Prepare homeBaseId → countryId map for destination filter ---
        $all_bases = $wpdb->get_results("SELECT id, base_data FROM {$charter_base_table}", ARRAY_A);
        $homeBase_map = [];
        foreach ($all_bases as $base) {
            $json = json_decode($base['base_data'], true);
            $homeBase_map[$base['id']] = $json['countryId'] ?? 0;
        }

        // --- 3️⃣ Group by yacht_id and collect all years for cross-year date filtering ---
        $yacht_map = [];
        foreach ($rows as $raw) {
            $yacht_id = $raw['yacht_id'];
            $year = $raw['availability_year'];

            if (!isset($yacht_map[$yacht_id])) {
                $yacht_map[$yacht_id] = [
                    'data' => $raw['data'],
                    'availability' => []
                ];
            }

            // Store availability by year
            $yacht_map[$yacht_id]['availability'][$year] = $raw['availability_string'];
        }

        $boats = [];

        foreach ($yacht_map as $yacht_id => $yacht_info) {
            $data = json_decode($yacht_info['data'], true);
            if (!is_array($data)) continue;

            // Merge availability info
            $data['yacht_id'] = $yacht_id;

            // --- 4️⃣ Apply filters ---
            $match = true;

            // --- 5️⃣ Date filter using binary string (supports date range & cross-year) ---
            if (!empty($filters['free_yacht']) && $match) {
                $date_input = $filters['free_yacht'];

                // Parse date range: "07.01.2026 to 30.01.2026" or single date "07.01.2026"
                if (strpos($date_input, ' to ') !== false) {
                    // Date range format
                    list($start_date_str, $end_date_str) = explode(' to ', $date_input);
                    $start_date = strtotime(trim($start_date_str));
                    $end_date = strtotime(trim($end_date_str));
                } else {
                    // Single date format
                    $start_date = strtotime($date_input);
                    $end_date = $start_date;
                }

                if ($start_date && $end_date) {
                    // Check if yacht has availability data for the requested period
                    $has_availability_data = false;
                    $all_days_available = true;

                    $current_timestamp = $start_date;

                    while ($current_timestamp <= $end_date) {
                        $current_year = (int) date('Y', $current_timestamp);
                        $day_of_year = (int) date('z', $current_timestamp); // 0 = Jan 1

                        // Get availability string for this year
                        $availability_str = $yacht_info['availability'][$current_year] ?? null;

                        if ($availability_str) {
                            $has_availability_data = true;

                            // Check length for leap year
                            $is_leap = date('L', $current_timestamp);
                            $expected_length = $is_leap ? 366 : 365;

                            if (strlen($availability_str) < $expected_length) {
                                $availability_str = str_pad($availability_str, $expected_length, '1');
                            }

                            // Check availability
                            $char = $availability_str[$day_of_year] ?? '1';
                            if ($char !== '0') { // '0' = available
                                $all_days_available = false;
                                break;
                            }
                        }

                        // Move to next day
                        $current_timestamp = strtotime('+1 day', $current_timestamp);
                    }

                    // Only filter out if yacht HAS availability data but is NOT available
                    // Skip filtering if yacht has no availability data (show it anyway)
                    if ($has_availability_data && !$all_days_available) {
                        $match = false;
                    }
                }
            }

            if (!empty($filters['category']) && $match) {
                $product_name = $filters['category'];
                $products = $data['products'] ?? [];

                $has_product = false;

                if (is_array($products)) {
                    foreach ($products as $product) {
                        if (
                                isset($product['name']) &&
                                strtolower($product['name']) === strtolower($product_name)
                        ) {
                            $has_product = true;
                            break;
                        }
                    }
                }

                if (!$has_product) {
                    $match = false;
                }
            }

            // Destination / country
            if (!empty($filters['location'])) {
                $homeBaseId = $data['homeBaseId'] ?? 0;
                $countryId = $homeBase_map[$homeBaseId] ?? 0;
                if ((int)$countryId !== (int)$filters['location']) {
                    $match = false;
                }
            }

            // Category filter (kind)
            if (!empty($filters['charter_type']) && ($data['kind'] ?? '') !== $filters['charter_type']) {
                $match = false;
            }

            // Persons
            if (!empty($filters['person']) && (int)$data['maxPeopleOnBoard'] !== (int)$filters['person']) {
                $match = false;
            }

            // Cabins
            if (!empty($filters['cabin']) && (int)$data['cabins'] !== (int)$filters['cabin']) {
                $match = false;
            }

            // Build year
            if (!empty($filters['year']) && (int)$data['year'] !== (int)$filters['year']) {
                $match = false;
            }

            if ($match) {
                // --- 🔥 Attach price data if available ---
                if ($price_data !== null && isset($price_data[$yacht_id])) {
                    $data['price_info'] = $price_data[$yacht_id];
                }

//                if ($offer_data && isset($offer_data[$yacht_id])) {
//                    $data['offer_info'] = $offer_data[$yacht_id];
//                }

                $boats[] = $data;
            }
        }

        // --- 6️⃣ Get total count AFTER filtering ---
        $total = count($boats);
        $pages = ceil($total / $per_page);

        // Debug logging
        error_log("Boat Filter Debug: Total boats after filtering = $total, Page = $paged, Per page = $per_page");

        // --- 7️⃣ Apply pagination on filtered results ---
        $offset = ($paged - 1) * $per_page;
        $paged_boats = array_slice($boats, $offset, $per_page);

        error_log("Boat Filter Debug: Showing " . count($paged_boats) . " boats on page $paged");

        return [
            'boats'     => $paged_boats,
            'paged'     => $paged,
            'pages'     => $pages,
            'per_page'  => $per_page,
            'total'     => $total,
            'has_prices' => ($price_data !== null) // Indicate if prices were fetched
        ];
    }

    // this api for offer
    public function get_all_yacht_offers_batch($date_from, $date_to)
    {
        $cache_key = 'yacht_offers_' . md5($date_from . '_' . $date_to);
        $cached = get_transient($cache_key);
        if ($cached !== false) return $cached;

        $query = http_build_query([
                'dateFrom' => $date_from . 'T00:00:00',
                'dateTo'   => $date_to . 'T00:00:00',
        ]);

        $url = "https://www.booking-manager.com/api/v2/offers?$query";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                        "accept: application/json",
                        "Authorization: Bearer " . $this->cread()['api_key'],
                ],
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!is_array($data)) return [];

        // Map by yachtId
        $offers = [];
        foreach ($data as $row) {
            $yachtId = (string)$row['yachtId'];

            if (!isset($offers[$yachtId])) {
                $offers[$yachtId] = [
                        'min_offer_price' => $row['price'],
                        'max_offer_price' => $row['price'],
                        'currency' => $row['currency'],
                        'max_discount' => $row['discountPercentage'],
                        'rows' => []
                ];
            }

            $offers[$yachtId]['min_offer_price'] = min(
                    $offers[$yachtId]['min_offer_price'],
                    $row['price']
            );

            $offers[$yachtId]['max_offer_price'] = max(
                    $offers[$yachtId]['max_offer_price'],
                    $row['price']
            );

            $offers[$yachtId]['max_discount'] = max(
                    $offers[$yachtId]['max_discount'],
                    $row['discountPercentage']
            );

            $offers[$yachtId]['rows'][] = $row;
        }

        set_transient($cache_key, $offers, HOUR_IN_SECONDS);
        return $offers;
    }


    public function get_all_yacht_prices_batch($date_from, $date_to, $yacht_id = null)
    {
        // Create cache key based on date range and yacht_id
        $cache_key = 'bm_prices_batch_' . md5($date_from . '_' . $date_to . '_' . ($yacht_id ?? 'all'));

        // Try to get from cache (1 hour)
        $cached = get_transient($cache_key);
        if ($cached !== false && is_array($cached)) {
            error_log("✅ Using cached prices (cache hit)");
            return $cached;
        }

        // If cache exists but is corrupt, delete it
        if ($cached !== false && !is_array($cached)) {
            error_log("⚠️ Corrupt cache detected, deleting...");
            delete_transient($cache_key);
        }

        error_log("📦 Cache miss - fetching from API");

        $query = http_build_query([
                'dateFrom' => $date_from . 'T00:00:00',
                'dateTo'   => $date_to . 'T00:00:00',
        ]);

        $api_url = "https://www.booking-manager.com/api/v2/prices?$query";

        error_log("🚀 Price API Request: $api_url");
        $request_start = microtime(true);

        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "Authorization: Bearer " . $this->cread()['api_key'],
            ],
            CURLOPT_TIMEOUT => 30, // Try 30 seconds first (Postman works in 6.6s)
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_ENCODING => 'gzip, deflate', // Enable compression like Postman
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false, // In case SSL is causing delays
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);

        // Get detailed timing info
        $curl_info = curl_getinfo($ch);
        $total_time = microtime(true) - $request_start;

        curl_close($ch);

        // Log detailed timing
        $download_size = $curl_info['size_download'] ?? 0;
        $size_mb = round($download_size / 1024 / 1024, 2);

        error_log(sprintf(
            "⏱️ Price API Timing: Total=%.2fs | DNS=%.2fs | Connect=%.2fs | Transfer=%.2fs | Size=%.2fMB | HTTP=%d",
            $total_time,
            $curl_info['namelookup_time'] ?? 0,
            $curl_info['connect_time'] ?? 0,
            $curl_info['starttransfer_time'] ?? 0,
            $size_mb,
            $http_code
        ));

        // Handle errors
        if ($curl_error || $http_code !== 200) {
            error_log("❌ Price API Error: HTTP $http_code - $curl_error");
            return null;
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            error_log("❌ Price API returned invalid JSON");
            return null;
        }

        $record_count = count($data);
        error_log("📊 Price API returned $record_count records");

        $processing_start = microtime(true);

        // 🔹 Filter by yacht if specific ID
        if ($yacht_id !== null) {
            $data = array_values(array_filter($data, function ($row) use ($yacht_id) {
                return isset($row['yachtId']) && (int)$row['yachtId'] === (int)$yacht_id;
            }));
            if (empty($data)) {
                error_log("⚠️ No prices found for yacht ID: $yacht_id");
                return null;
            }

            $prices = array_column($data, 'price');
            $rows = $data;

            $result = [
                    'currency' => $rows[0]['currency'] ?? '',
                    'min'      => min($prices),
                    'max'      => max($prices),
                    // 🚫 Don't cache 'rows' - it's too large and causes MySQL issues
                    // 'rows'     => $rows,
            ];

            $processing_time = microtime(true) - $processing_start;
            error_log("✅ Processed single yacht in " . round($processing_time, 3) . "s");

            // Cache for 1 hour (much smaller now without 'rows')
            set_transient($cache_key, $result, HOUR_IN_SECONDS);
            return $result;
        }

        // 🔹 Multiple yachts: return associative array keyed by yachtId
        $all = [];
        foreach ($data as $row) {
            $yid = (string)($row['yachtId'] ?? '');
            if (!$yid) continue;

            if (!isset($all[$yid])) {
                $all[$yid] = [
                        'currency' => $row['currency'] ?? '',
                        'min'      => $row['price'],
                        'max'      => $row['price'],
                        // 🚫 Don't store 'rows' - it's too large
                        // 'rows'     => [$row],
                ];
            } else {
                $all[$yid]['min'] = min($all[$yid]['min'], $row['price']);
                $all[$yid]['max'] = max($all[$yid]['max'], $row['price']);
                // 🚫 Don't append to 'rows'
                // $all[$yid]['rows'][] = $row;
            }
        }

        $processing_time = microtime(true) - $processing_start;
        $yacht_count = count($all);
        $cache_size_kb = round(strlen(serialize($all)) / 1024, 2);
        error_log("✅ Processed $yacht_count yachts in " . round($processing_time, 3) . "s | Cache size: {$cache_size_kb}KB");

        // Cache for 1 hour (much smaller without 'rows' data)
        $cache_success = set_transient($cache_key, $all, HOUR_IN_SECONDS);

        if (!$cache_success) {
            error_log("⚠️ Failed to cache prices (data may be too large)");
        } else {
            error_log("✅ Successfully cached $yacht_count yacht prices");
        }

        return $all;
    }

    /**
     * Fetch price details with 'rows' for a SINGLE yacht (for boat details page)
     * This re-fetches from API to get full row data, not from cache
     *
     * @param string $yacht_id The yacht ID
     * @param string $date_from Date from (Y-m-d)
     * @param string $date_to Date to (Y-m-d)
     * @return array|null ['min' => X, 'max' => Y, 'currency' => 'EUR', 'rows' => [...]]
     */
    public function get_single_yacht_price_details($yacht_id, $date_from, $date_to)
    {
        // Create cache key for this specific yacht and date range
        $cache_key = 'yacht_price_details_' . $yacht_id . '_' . md5($date_from . '_' . $date_to);

        // Try to get from cache (1 hour)
        $cached = get_transient($cache_key);
        if ($cached !== false && is_array($cached)) {
            error_log("✅ Using cached yacht price details (yacht: $yacht_id)");
            return $cached;
        }

        error_log("📦 Fetching price details for yacht: $yacht_id from $date_from to $date_to");

        // Build API URL
        $query = http_build_query([
            'yachtId' => $yacht_id,
            'dateFrom' => $date_from . 'T00:00:00',
            'dateTo'   => $date_to . 'T00:00:00',
        ]);

        $api_url = "https://www.booking-manager.com/api/v2/prices?$query";

        error_log("🚀 Price Details API Request: $api_url");
        $request_start = microtime(true);

        // Fetch from API
        $ch = curl_init($api_url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "Authorization: Bearer " . $this->cread()['api_key'],
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_info = curl_getinfo($ch);
        $total_time = microtime(true) - $request_start;

        curl_close($ch);

        // Log timing
        error_log(sprintf(
            "⏱️ Price Details API: Total=%.2fs | HTTP=%d",
            $total_time,
            $http_code
        ));

        // Handle errors
        if ($curl_error || $http_code !== 200) {
            error_log("❌ Price Details API Error: HTTP $http_code - $curl_error");
            return null;
        }

        $data = json_decode($response, true);

        if (!is_array($data)) {
            error_log("❌ Price Details API returned invalid JSON");
            return null;
        }

        $total_records = count($data);
        error_log("📊 Price Details API returned $total_records total records");

        // Filter only this yacht's prices
        $yacht_prices = array_filter($data, function($row) use ($yacht_id) {
            return isset($row['yachtId']) && (string)$row['yachtId'] === (string)$yacht_id;
        });

        $yacht_prices = array_values($yacht_prices); // Re-index array

        if (empty($yacht_prices)) {
            error_log("⚠️ No prices found for yacht ID: $yacht_id");
            return null;
        }

        // Calculate min/max prices
        $prices = array_column($yacht_prices, 'price');
        $min_price = min($prices);
        $max_price = max($prices);
        $currency = $yacht_prices[0]['currency'] ?? 'EUR';

        $result = [
            'currency' => $currency,
            'min' => $min_price,
            'max' => $max_price,
            'rows' => $yacht_prices, // ✅ Include full rows for price table!
        ];

        error_log("✅ Found " . count($yacht_prices) . " price records for yacht $yacht_id");

        // Cache for 1 hour (smaller data - just one yacht)
        $cache_success = set_transient($cache_key, $result, HOUR_IN_SECONDS);

        if ($cache_success) {
            error_log("✅ Cached yacht price details for yacht $yacht_id");
        } else {
            error_log("⚠️ Failed to cache yacht price details");
        }

        return $result;
    }

    // Fetch all boat book reservation list
    function fetch_book_reservation( $id = null){

        global $wpdb;
        $table = $wpdb->prefix . 'boat_book_request';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC", ARRAY_A);

        // If specific ID is requested
        if ($id !== null) {
            foreach ($results as $row) {
                if (isset($row['boat_id']) && $row['boat_id'] == $id) {
                    $row['book_data'] = json_decode($row['book_data'], true);
                    return $row;
                }
            }
            return null;
        }

        // Unserialize model_data for all rows
        foreach ($results as &$row) {
            $row['book_data'] = json_decode($row['book_data'], true);
        }

        return $results;
    }

     // Fetch boat book reservation for mail send
    function fetch_book_reservation_for_mail_send(){

        global $wpdb;
        $table = $wpdb->prefix . 'boat_book_request';

        // Get all rows from the table
        $results = $wpdb->get_results("SELECT * FROM $table WHERE mail_status = 'pending'", ARRAY_A);

        // Unserialize model_data for all rows
        foreach ($results as &$row) {
            $row['book_data'] = json_decode($row['book_data'], true);
        }

        return $results;
    }

    // Preloader function
    function bl_render_boat_overlay_preloader() {
        ob_start();
        ?>
        <div class="boat-overlay-preloader" id="boat-overlay-preloader">
            <div class="boat-preloader-inner">
                <div class="wave"></div>
                <div class="boat">⛵</div>
                <div class="loading-text"><?php esc_html_e('Please wait...', 'boat-listing'); ?></div>
            </div>
        </div>
        <style>

            .boat-lists-loader {
                background: #ffffff82;
                height: 100%;
                position: absolute;
                width: 100%;
                z-index: 99;
            }

            .boat-overlay-preloader {
                position: relative;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background:rgba(255, 255, 255, 0.61);
                display: flex;
                align-items: flex-start; /* ⬅ Push loader to top */
                justify-content: center;
                z-index: 99;
                pointer-events: none;
                padding-top: 50px;
            }

            .boat-preloader-inner {
                position: fixed;
                top: 50%;
                text-align: center;
                font-family: sans-serif;
            }

            .boat-preloader-inner .wave {
                position: absolute;
                bottom: -20px;
                width: 200%;
                height: 30px;
                background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 120 28" xmlns="http://www.w3.org/2000/svg"><path fill="%2300bcd4" d="M0,15 C30,30 90,0 120,15 L120,30 L0,30 Z"></path></svg>') repeat-x;
                animation: wave 3s ease-in-out infinite alternate;
            }

            @keyframes wave {
                from { transform: translateX(0); }
                to { transform: translateX(-50%); }
            }

            .boat-preloader-inner .boat {
                font-size: 48px;
                animation: floatBoat 2s ease-in-out infinite;
                position: relative;
                z-index: 2;
            }

            @keyframes floatBoat {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }

            .loading-text {
                margin-top: 10px;
                font-size: 18px;
                color: #004d40;
                font-weight: bold;
            }
        </style>
        <?php
        return ob_get_clean();
    }


    function icons(){

        $icon = [
            'location' => '<i class="ri-map-pin-line"></i>',
            'model' => '<i class="ri-box-3-line"></i>',
            'company' => '<i class="ri-building-line"></i>',
            'filter' => '<i class="ri-equalizer-line"></i>',
            'person' => '<i class="ri-user-add-line"></i>',
            'length' => '<i class="ri-expand-horizontal-s-fill"></i>',
            'date' => '<i class="ri-calendar-line"></i>',
            'cabin' => '<i class="ri-hotel-bed-line"></i>',
            'shower' => '<i class="ri-heavy-showers-line"></i>',
            'salon' => '<i class="ri-sword-line"></i>',
            'arrowright' => '<i class="ri-arrow-right-line"></i>',
            'arrowleft' => '<i class="ri-arrow-left-line"></i>',
            'question_round' => '<i class="ri-question-line"></i>',
            'email_check' => '<i class="ri-mail-check-line"></i>',
            'phone' => '<i class="ri-phone-line"></i>',
            'message' => '<i class="ri-message-3-line"></i>',
            'headphone' => '<i class="ri-customer-service-line"></i>',
            'charter_type' => '<i class="ri-sailboat-line"></i>',
            'category' => '<i class="ri-menu-search-line"></i>',
            'euro' => '<i class="ri-money-euro-circle-line"></i>',
        ];

        return $icon;
    }

    function button_spinner() {

        ob_start();
        ?>
        
            <span class="bl-button-spnner"></span>
            <style>
                .bl-button-spnner {
                    width: 15px;
                    height: 15px;   
                    border: 3px solid #cccccc;
                    border-top-color:rgb(255, 255, 255);  /* WP blue */
                    border-radius: 50%;
                    animation: spin 0.8s linear infinite;
                    display: inline-block;
                    vertical-align: middle;
                }

                @keyframes spin {
                    to { transform: rotate(360deg); }
                }
            </style>

        <?php

        return ob_get_clean();
    }
}

new Boat_Listing_Helper();