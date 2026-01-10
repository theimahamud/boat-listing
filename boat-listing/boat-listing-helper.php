<?php

class Boat_Listing_Helper{

    // Cache duration in seconds (15 minutes)
    private $cache_duration = 900;

    private function cread(): array
    {
        $cread = ['api_key' => get_option('bl_api_key'),];

        return $cread;
    }

    /**
     * Get Boat Product Types
     */
    public function getBoatProductTypes(): array
    {
        return [
            'Bareboat'    => 'Bareboat',
            'Crewed'    => 'Crewed',
            'Cabin'    => 'Cabin',
            'Flotilla'    => 'Flotilla',
            'Power'    => 'Power',
            'Berth'    => 'Berth',
            'Regatta'    => 'Regatta',
            'AllInclusive'    => 'All Inclusive',
            'DailyCharter'    => 'Daily Charter',
        ];
    }

    public function icons(): array
    {
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

    /**
     * Sort countries by priority names
     */
    function sortPriorityCountries(): array
    {
        $countries = $this->fetch_country();
        $regions   = $this->fetch_regions();

        $priorityNames = [
            // 'British Virgin Islands',
            // 'United States Virgin Islands',
        ];

        $priorityCountries   = [];
        $remainingCountries  = [];

        // Separate priority countries from the rest
        foreach ($countries as $country) {
            $countryName = $country['country_data']['name'] ?? '';

            if (in_array($countryName, $priorityNames, true)) {
                $priorityCountries[] = $country;
            } else {
                $remainingCountries[] = $country;
            }
        }

        // Find Caribbean region first
        $firstRegion  = null;
        $otherRegions = [];

        foreach ($regions as $region) {
            if (($region['regions_data']['name'] ?? '') === 'Caribbean') {
                $firstRegion = $region;
            } else {
                $otherRegions[] = $region;
            }
        }

        // Rebuild regions array with Caribbean first
        $regionsOrdered = [];
        if ($firstRegion) {
            $regionsOrdered[] = $firstRegion;
        }
        $regionsOrdered = array_merge($regionsOrdered, $otherRegions);

        // ✅ Return both arrays
        return [
            'regions'   => $regionsOrdered,
            'countries' => $remainingCountries,
        ];
    }

    public function getBoatYears(): array
    {
        $year_list = [];

        foreach ($this->fetch_all_boats() as $boat) {
            $data = $boat['data'];
            if (!empty($data['year'])) {
                $year_list[] = (int) $data['year'];
            }
        }

        $year_list = array_unique($year_list);
        rsort($year_list, SORT_NUMERIC);

        return $year_list;
    }

    public function getBoatCabins(): array
    {
        $cabins_list = [];
        foreach ($this->fetch_all_boats() as $cabins) {
         $c_data = $cabins['data'];
            if (isset($c_data['cabins']) && $c_data['cabins'] > 0) {
                $cabins_list[] = (int) $c_data['cabins'];
            }
        }

        return $this->sortBoatNumberValue($cabins_list);
    }


    public function getBoatPersons(): array
    {
        $person_list = [];

        foreach ($this->fetch_all_boats() as $yacht) {
            $p_data = $yacht['data'];

            if (!empty($p_data['maxPeopleOnBoard']) && (int)$p_data['maxPeopleOnBoard'] > 0) {
                $person_list[] = (int)$p_data['maxPeopleOnBoard'];
            }
        }

       return $this->sortBoatNumberValue($person_list);
    }

    public function sortBoatNumberValue(array $data): array
    {
        $boatNumberValue = array_unique($data);
        sort($boatNumberValue, SORT_NUMERIC);
        return $boatNumberValue;
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
     * Get cached API response or fetch new one
     */
    private function get_cached_api_response($cache_key, $api_call_callback) {
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            error_log("✅ Cache HIT for key: {$cache_key}");
            return $cached;
        }

        error_log("❌ Cache MISS for key: {$cache_key} - Fetching from API...");
        $response = call_user_func($api_call_callback);

        if (!empty($response)) {
            set_transient($cache_key, $response, $this->cache_duration);
            error_log("💾 Cached response for key: {$cache_key}");
        }

        return $response;
    }

    /**
     * Generate cache key from filters
     */
    private function generate_cache_key($country, $productName, $dateFrom, $dateTo, $filters = []) {
        $key_parts = [
                'boat_offers',
                $country ?: 'all',
                $productName ?: 'all',
                $dateFrom ?: 'nodate',
                $dateTo ?: 'nodate'
        ];

        // Add relevant filters to cache key
        $relevant_filters = ['person', 'cabin', 'year', 'charter_type', 'berths', 'wc'];
        foreach ($relevant_filters as $filter) {
            if (!empty($filters[$filter])) {
                $key_parts[] = $filter . '_' . $filters[$filter];
            }
        }

        return 'bl_' . md5(implode('_', $key_parts));
    }

    /**
     * Optimized boat_filters with caching and batch processing
     */
    public function boat_filters($country, $productName, $dateFrom, $dateTo, $paged = 1, $per_page = 10, $filters = []): array
    {
        $start_time = microtime(true);

        // Generate cache key
        $cache_key = $this->generate_cache_key($country, $productName, $dateFrom, $dateTo, $filters);

        // Try to get cached offers
        $offers = $this->get_cached_api_response($cache_key, function() use ($country, $productName, $dateFrom, $dateTo, $filters) {
            return $this->fetch_boat_offers($country, $productName, $dateFrom, $dateTo, $filters);
        });

        if (empty($offers) || !is_array($offers)) {
            error_log("❌ No offers returned");
            return [
                    'boats' => [],
                    'paged' => $paged,
                    'pages' => 0,
                    'per_page' => $per_page,
                    'total' => 0
            ];
        }

        error_log("📊 Processing " . count($offers) . " offers");

        // Batch process: Get all yacht IDs at once
        $yacht_ids = array_unique(array_filter(array_column($offers, 'yachtId')));

        if (empty($yacht_ids)) {
            error_log("❌ No yacht IDs found in offers");
            return ['boats' => [], 'paged' => $paged, 'pages' => 0, 'per_page' => $per_page, 'total' => 0];
        }

        // Batch fetch boats from database
        global $wpdb;
        $boats_table = $wpdb->prefix . 'boats';
        $placeholders = implode(',', array_fill(0, count($yacht_ids), '%d'));
        $query = "SELECT id, data FROM {$boats_table} WHERE id IN ($placeholders)";
        $results = $wpdb->get_results($wpdb->prepare($query, ...$yacht_ids), ARRAY_A);

        // Create lookup map for faster access
        $boats_map = [];
        foreach ($results as $row) {
            $data = json_decode($row['data'], true);
            if (is_array($data)) {
                $boats_map[$row['id']] = $data;
            }
        }

        error_log("🗄️ Found " . count($boats_map) . " boats in database");

        // Process offers with pre-fetched boat data
        $boats = [];
        foreach ($offers as $offer) {
            $yacht_id = isset($offer['yachtId']) ? intval($offer['yachtId']) : 0;

            if (!$yacht_id || !isset($boats_map[$yacht_id])) {
                continue;
            }

            $data = $boats_map[$yacht_id];

            // Apply local filters (optimized with early returns)
            if (!$this->apply_local_filters($data, $filters)) {
                continue;
            }

            // Merge offer data
            $data['offer'] = $offer;

            if (!empty($offer['dateFrom'])) {
                $ts = strtotime($offer['dateFrom']);
                if ($ts !== false) {
                    $data['availability_year'] = date('Y', $ts);
                }
            }

            $boats[] = $data;
        }

        error_log("✅ Filtered to " . count($boats) . " matching boats");

        // Pagination
        $total = count($boats);
        $pages = ($per_page > 0) ? (int) ceil($total / $per_page) : 1;
        $paged = max(1, intval($paged));
        $offset = ($paged - 1) * $per_page;
        $paged_boats = array_slice($boats, $offset, $per_page);

        $execution_time = round((microtime(true) - $start_time) * 1000, 2);
        error_log("⏱️ boat_filters completed in {$execution_time}ms");

        return [
                'boats' => $paged_boats,
                'paged' => $paged,
                'pages' => $pages,
                'per_page' => $per_page,
                'total' => $total
        ];
    }

    /**
     * Optimized filter application
     */
    private function apply_local_filters($data, $filters) {
        // Charter type filter
        if (!empty($filters['charter_type']) && !empty($data['kind'])) {
            if (stripos($data['kind'], $filters['charter_type']) === false) {
                return false;
            }
        }

        // Person filter
        if (!empty($filters['person']) && !empty($data['maxPeopleOnBoard'])) {
            if (intval($data['maxPeopleOnBoard']) < intval($filters['person'])) {
                return false;
            }
        }

        // Year filter
        if (!empty($filters['year']) && !empty($data['year'])) {
            if (intval($data['year']) != intval($filters['year'])) {
                return false;
            }
        }

        // Cabin filter
        if (!empty($filters['cabin']) && !empty($data['cabins'])) {
            if (intval($data['cabins']) < intval($filters['cabin'])) {
                return false;
            }
        }

        // Berths filter
        if (!empty($filters['berths']) && !empty($data['berths'])) {
            if (intval($data['berths']) < intval($filters['berths'])) {
                return false;
            }
        }

        // WC filter
        if (!empty($filters['wc']) && !empty($data['wc'])) {
            if (intval($data['wc']) < intval($filters['wc'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Optimized fetch_boat_offers with connection pooling
     */
    public function fetch_boat_offers($country, $productName, $dateFrom, $dateTo, $filters = [])
    {
        $params = [];

        // Build essential parameters
        if (!empty($country)) $params['country'] = $country;
        if (!empty($productName)) $params['productName'] = $productName;
        if (!empty($dateFrom)) $params['dateFrom'] = $dateFrom;
        if (!empty($dateTo)) $params['dateTo'] = $dateTo;

        // Calculate trip duration
        if (!empty($dateFrom) && !empty($dateTo)) {
            try {
                $start = new DateTime($dateFrom);
                $end = new DateTime($dateTo);
                $duration = $start->diff($end)->days;
                if ($duration > 0) {
                    $params['tripDuration'] = $duration;
                }
            } catch (Exception $e) {
                error_log("❌ Date parsing error: " . $e->getMessage());
            }
        }

        // Add optional filters
        if (!empty($filters['person'])) $params['passengersOnBoard'] = intval($filters['person']);
        if (!empty($filters['cabin'])) $params['minCabins'] = intval($filters['cabin']);

        if (!empty($filters['year'])) {
            $year = intval($filters['year']);
            $params['minYearOfBuild'] = $year;
            $params['maxYearOfBuild'] = $year;
        }

        // Add advanced filters
        if (!empty($filters['berths'])) $params['minBerths'] = intval($filters['berths']);
        if (!empty($filters['wc'])) $params['minHeads'] = intval($filters['wc']);
        if (!empty($filters['min_length'])) $params['minLength'] = floatval($filters['min_length']);
        if (!empty($filters['max_length'])) $params['maxLength'] = floatval($filters['max_length']);

        // Default parameters
        $params['currency'] = 'EUR';
        $params['showOptions'] = 'true';
        $params['flexibility'] = $filters['flexibility'] ?? 1;

        // Clean params
        $params = array_filter($params, function($value) {
            return $value !== null && $value !== '';
        });

        $query = http_build_query($params);
        $api_url = "https://www.booking-manager.com/api/v2/offers?$query";

        error_log("🌐 API URL: " . $api_url);

        $ch = curl_init($api_url);

        curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                        "accept: application/json",
                        "Authorization: Bearer " . $this->cread()['api_key'],
                        "Connection: keep-alive",
                        "Accept-Encoding: gzip, deflate, br", // Enable compression
                ],
                CURLOPT_TIMEOUT => 30, // Reduced timeout
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_ENCODING => '', // Auto-decode gzip/deflate
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true, // Re-enable for security
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_TCP_KEEPALIVE => 1,
                CURLOPT_TCP_KEEPIDLE => 30,
                CURLOPT_TCP_KEEPINTVL => 10,
                CURLOPT_NOSIGNAL => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0, // Use HTTP/2 if available
        ]);

        $start_time = microtime(true);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $execution_time = round((microtime(true) - $start_time) * 1000, 2);
        curl_close($ch);

        error_log("⏱️ API call completed in {$execution_time}ms (HTTP {$http_code})");

        if ($curl_error) {
            error_log("❌ CURL Error: " . $curl_error);
            return [];
        }

        if ($response === false || $http_code !== 200) {
            error_log("❌ API call failed - HTTP {$http_code}");
            return [];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("❌ JSON decode error: " . json_last_error_msg());
            return [];
        }

        error_log("✅ API returned " . count($data) . " offers");
        return $data;
    }

    /**
     * Clear cache (call this when you update boat data)
     */
    public function clear_cache($pattern = 'bl_*') {
        global $wpdb;
        $wpdb->query($wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",'%_transient_' . $pattern . '%'));
        error_log("🗑️ Cache cleared for pattern: {$pattern}");
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

    /**
     * Manual API test function - call this to test the API directly
     * Usage: $helper = new Boat_Listing_Helper(); $helper->test_api();
     */
    public function test_api() {
        error_log("🧪 === MANUAL API TEST STARTING ===");

        // Test 1: Greece with Bareboat
        $test1 = $this->fetch_boat_offers('GR', 'Bareboat', '', '');
        error_log("🧪 Test 1 (GR, Bareboat): " . (is_array($test1) ? count($test1) . ' results' : 'Failed'));

        // Test 2: No filters
        $test2 = $this->fetch_boat_offers('', '', '', '');
        error_log("🧪 Test 2 (No filters): " . (is_array($test2) ? count($test2) . ' results' : 'Failed'));

        // Test 3: Only country
        $test3 = $this->fetch_boat_offers('GR', '', '', '');
        error_log("🧪 Test 3 (GR only): " . (is_array($test3) ? count($test3) . ' results' : 'Failed'));

        error_log("🧪 === MANUAL API TEST COMPLETE ===");

        return [
            'test1' => $test1,
            'test2' => $test2,
            'test3' => $test3
        ];
    }

    /**
     * Check which yacht IDs from API offers exist in local database
     */
    public function check_yacht_id_matching($offers) {
        global $wpdb;
        $boats_table = $wpdb->prefix . 'boats';

        $api_yacht_ids = array_map(function($offer) { return $offer['yachtId'] ?? 0; }, $offers);
        $api_yacht_ids = array_filter($api_yacht_ids); // Remove zeros

        if (empty($api_yacht_ids)) {
            error_log("❌ No yacht IDs found in API offers");
            return [];
        }

        // Check which IDs exist in database
        $placeholders = implode(',', array_fill(0, count($api_yacht_ids), '%d'));
        $query = "SELECT id FROM {$boats_table} WHERE id IN ($placeholders)";
        $found_ids = $wpdb->get_col($wpdb->prepare($query, ...$api_yacht_ids));

        $missing_ids = array_diff($api_yacht_ids, $found_ids);

        error_log("🔍 Yacht ID Matching Analysis:");
        error_log("  - API yacht IDs: " . implode(', ', $api_yacht_ids));
        error_log("  - Found in DB: " . implode(', ', $found_ids));
        error_log("  - Missing from DB: " . implode(', ', $missing_ids));
        error_log("  - Match rate: " . count($found_ids) . "/" . count($api_yacht_ids));

        return [
            'api_ids' => $api_yacht_ids,
            'found_ids' => $found_ids,
            'missing_ids' => $missing_ids
        ];
    }

    /**
     * Search local boats database using filters (fallback when API offers don't match)
     */
    public function search_local_boats($filters, $per_page = 10, $paged = 1) {
        global $wpdb;
        $boats_table = $wpdb->prefix . 'boats';

        error_log("🔍 Searching local database with filters...");

        // Get all boats from database
        $all_boats = $this->fetch_all_boats();
        $filtered_boats = [];

        foreach ($all_boats as $boat_row) {
            $data = $boat_row['data'];

            // Apply filters using correct field mappings
            if (!empty($filters['charter_type']) &&
                !empty($data['kind']) &&
                stripos($data['kind'], $filters['charter_type']) === false) {
                continue;
            }

            if (!empty($filters['person']) &&
                !empty($data['maxPeopleOnBoard']) &&
                intval($data['maxPeopleOnBoard']) < intval($filters['person'])) {
                continue;
            }

            if (!empty($filters['year']) &&
                !empty($data['year']) &&
                intval($data['year']) != intval($filters['year'])) {
                continue;
            }

            if (!empty($filters['cabin']) &&
                !empty($data['cabins']) &&
                intval($data['cabins']) < intval($filters['cabin'])) {
                continue;
            }

            // Add boat to filtered results (without offer data)
            $filtered_boats[] = $data;
        }

        // Pagination
        $total = count($filtered_boats);
        $pages = ($per_page > 0) ? (int) ceil($total / $per_page) : 1;
        $paged = max(1, intval($paged));
        $offset = ($paged - 1) * $per_page;
        $paged_boats = array_slice($filtered_boats, $offset, $per_page);

        error_log("  - Total local matches: " . $total);
        error_log("  - Boats on this page: " . count($paged_boats));

        return [
            'boats' => $paged_boats,
            'paged' => $paged,
            'pages' => $pages,
            'per_page' => $per_page,
            'total' => $total
        ];
    }

    /**
     * Get single yacht offer details for a specific boat with all search filters
     */
    public function get_single_yacht_offer_details($boat_id, $date_from, $date_to, $filters = [])
    {
        error_log("🔍 Getting specific yacht offer for ID: {$boat_id} with all filters");
        error_log("📅 Date range: {$date_from} to {$date_to}");
        error_log("🎯 Filters applied: " . print_r($filters, true));

        // If no date range provided, return basic structure
        if (empty($date_from) || empty($date_to)) {
            error_log("❌ No date range provided for price lookup");
            return [
                'min' => 'N/A',
                'max' => 'N/A',
                'currency' => 'EUR',
                'rows' => []
            ];
        }

        try {
            // Get boat data to find country and other details needed for API call
            $boat_data = $this->fetch_all_boats($boat_id);
            if (empty($boat_data)) {
                error_log("❌ Boat not found in database: {$boat_id}");
                return [
                    'min' => 'N/A',
                    'max' => 'N/A',
                    'currency' => 'EUR',
                    'rows' => []
                ];
            }

            $boat = $boat_data['data'] ?? [];

            // Use country from filters, or detect from boat's home base
            $country = $filters['country'] ?? '';
            if (empty($country) && !empty($boat['homeBase'])) {
                // Enhanced country mapping based on common bases
                $base_country_mapping = [
                    'Athens' => 'GR', 'Alimos' => 'GR', 'Piraeus' => 'GR', 'Lavrion' => 'GR',
                    'Split' => 'HR', 'Dubrovnik' => 'HR', 'Zadar' => 'HR', 'Trogir' => 'HR',
                    'Marmaris' => 'TR', 'Bodrum' => 'TR', 'Fethiye' => 'TR', 'Gocek' => 'TR',
                    'Palermo' => 'IT', 'Naples' => 'IT', 'Rome' => 'IT', 'Salerno' => 'IT',
                    'Barcelona' => 'ES', 'Palma' => 'ES', 'Valencia' => 'ES', 'Ibiza' => 'ES',
                    'Cannes' => 'FR', 'Nice' => 'FR', 'Marseille' => 'FR', 'Saint Tropez' => 'FR'
                ];

                foreach ($base_country_mapping as $base_name => $code) {
                    if (stripos($boat['homeBase'], $base_name) !== false) {
                        $country = $code;
                        break;
                    }
                }
            }

            // Default to Greece if we can't determine
            if (empty($country)) {
                $country = 'GR';
                error_log("⚠️ Could not determine country, defaulting to GR");
            }

            // Use productName from filters or default
            $productName = $filters['productName'] ?? 'Bareboat';

            error_log("📡 Calling API for specific yacht: {$boat_id} in country: {$country} with product: {$productName}");

            // Call the API with all filters including the specific yacht ID
            $offers = $this->fetch_boat_offers($country, $productName, $date_from, $date_to, $filters);

            if (empty($offers)) {
                error_log("❌ No offers found for yacht ID {$boat_id} with given filters");
                return [
                    'min' => 'N/A',
                    'max' => 'N/A',
                    'currency' => 'EUR',
                    'rows' => []
                ];
            }

            // Process offers to extract price information for this specific yacht
            $prices = [];
            $currency = 'EUR';

            error_log("🔍 Processing " . count($offers) . " offers for yacht {$boat_id}");

            foreach ($offers as $offer) {
                if (isset($offer['yachtId']) && $offer['yachtId'] == $boat_id) {

                    $starting_price = floatval($offer['startPrice'] ?? $offer['price'] ?? 0);
                    $final_price = floatval($offer['price'] ?? 0);
                    $obligatory_extras = floatval($offer['obligatoryExtrasPrice'] ?? 0);

                    $price_info = [
                        'dateFrom' => $offer['dateFrom'] ?? '',
                        'dateTo' => $offer['dateTo'] ?? '',
                        'price' => $starting_price,                 // Show starting price
                        'finalPrice' => $final_price,               // Keep final price for reference
                        'obligatoryExtrasPrice' => $obligatory_extras,
                        'totalPrice' => $starting_price,            // Display starting price
                        'currency' => $offer['currency'] ?? 'EUR',
                        'securityDeposit' => floatval($offer['securityDeposit'] ?? 0),
                        'yacht' => $offer['yacht'] ?? '',
                        'startBase' => $offer['startBase'] ?? '',
                        'endBase' => $offer['endBase'] ?? '',
                        'product' => $offer['product'] ?? 'Bareboat'
                    ];

                    $prices[] = $price_info;
                    $currency = $offer['currency'] ?? 'EUR';

                    error_log("💰 Offer found: Starting Price={$starting_price} {$currency}");
                }
            }

            if (empty($prices)) {
                error_log("❌ No matching offers found for yacht {$boat_id} with applied filters");
                return [
                    'min' => 'N/A',
                    'max' => 'N/A',
                    'currency' => $currency,
                    'rows' => []
                ];
            }

            // Calculate min and max prices (using starting prices for display)
            $all_starting_prices = array_column($prices, 'price');
            $min_price = min($all_starting_prices);
            $max_price = max($all_starting_prices);

            error_log("✅ Found " . count($prices) . " specific offers for yacht {$boat_id}");
            error_log("💰 Starting price range: {$min_price} - {$max_price} {$currency}");

            return [
                'min' => number_format($min_price, 2),
                'max' => number_format($max_price, 2),
                'currency' => $currency,
                'rows' => $prices
            ];

        } catch (Exception $e) {
            error_log("❌ Error getting price details for yacht {$boat_id}: " . $e->getMessage());
            return [
                'min' => 'N/A',
                'max' => 'N/A',
                'currency' => 'EUR',
                'rows' => []
            ];
        }
    }
}

new Boat_Listing_Helper();