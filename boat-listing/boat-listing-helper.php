<?php

class Boat_Listing_Helper{

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

    public function boat_filters($country, $productName, $dateFrom, $dateTo, $paged = 1, $per_page = 10, $filters = []): array
    {
        global $wpdb;

        // Debug logging
        error_log("🔍 DEBUG boat_filters called with:");
        error_log("Country: " . ($country ?: 'empty'));
        error_log("ProductName: " . ($productName ?: 'empty'));
        error_log("DateFrom: " . ($dateFrom ?: 'empty'));
        error_log("DateTo: " . ($dateTo ?: 'empty'));

        // Debug filters
        error_log("🔍 Local filters to apply:");
        foreach($filters as $key => $value) {
            if (!empty($value)) {
                error_log("  - {$key}: {$value}");
            }
        }

        // Fetch offers from Booking Manager API
        error_log("📡 Calling fetch_boat_offers API...");
        $offers = $this->fetch_boat_offers($country, $productName, $dateFrom, $dateTo, $filters);

        // Debug API response
        error_log("📊 API Response type: " . gettype($offers));
        if (is_array($offers)) {
            error_log("📊 API Response count: " . count($offers));
            error_log("📊 First offer sample: " . print_r(array_slice($offers, 0, 1), true));
        } else {
            error_log("📊 API Response (non-array): " . print_r($offers, true));
        }

        // Normalize response - expect an array of offers
        if (empty($offers) || !is_array($offers)) {
            error_log("❌ API returned empty or invalid data");
            return [
                'boats'     => [],
                'paged'     => $paged,
                'pages'     => 0,
                'per_page'  => $per_page,
                'total'     => 0
            ];
        }

        $boats = [];
        $boats_table = $wpdb->prefix . 'boats';

        // Check total boats in database for debugging
        $total_boats_in_db = $wpdb->get_var("SELECT COUNT(*) FROM {$boats_table}");
        error_log("📊 Total boats in database: " . $total_boats_in_db);

        // Get sample yacht IDs from database
        $sample_ids = $wpdb->get_col("SELECT id FROM {$boats_table} LIMIT 10");
        error_log("📊 Sample yacht IDs in database: " . implode(', ', $sample_ids));

        // Get yacht IDs from API offers
        $api_yacht_ids = array_map(function($offer) { return $offer['yachtId'] ?? 0; }, $offers);
        error_log("📊 Yacht IDs from API: " . implode(', ', $api_yacht_ids));

        // Run yacht ID matching analysis
        $this->check_yacht_id_matching($offers);

        foreach ($offers as $offer) {
            // Each offer should contain a yachtId that maps to local boats.id
            $yacht_id = isset($offer['yachtId']) ? intval($offer['yachtId']) : 0;

            error_log("🔍 Processing offer - YachtID: " . $yacht_id);

            if (!$yacht_id) {
                error_log("  ❌ No yachtId in offer");
                continue;
            }

            // Try to fetch the local boat row
            $row = $wpdb->get_row($wpdb->prepare("SELECT data FROM {$boats_table} WHERE id = %d", $yacht_id), ARRAY_A);

            if (empty($row) || empty($row['data'])) {
                error_log("  ❌ YachtID {$yacht_id} not found in local database");
                continue;
            }

            error_log("  ✅ YachtID {$yacht_id} found in database");

            $data = json_decode($row['data'], true);
            if (!is_array($data)) {
                error_log("  ❌ Invalid JSON data for YachtID {$yacht_id}");
                continue;
            }

            // Apply local database filters using correct field mappings
            if (!empty($filters['charter_type']) &&
                !empty($data['kind']) &&
                stripos($data['kind'], $filters['charter_type']) === false) {
                error_log("  ❌ Charter type filter: {$filters['charter_type']} not matching {$data['kind']}");
                continue;
            }

            if (!empty($filters['person']) &&
                !empty($data['maxPeopleOnBoard']) &&
                intval($data['maxPeopleOnBoard']) < intval($filters['person'])) {
                error_log("  ❌ Person filter: {$filters['person']} exceeds maxPeopleOnBoard {$data['maxPeopleOnBoard']}");
                continue;
            }

            if (!empty($filters['year']) &&
                !empty($data['year']) &&
                intval($data['year']) != intval($filters['year'])) {
                error_log("  ❌ Year filter: {$filters['year']} not matching {$data['year']}");
                continue;
            }

            if (!empty($filters['cabin']) &&
                !empty($data['cabins']) &&
                intval($data['cabins']) < intval($filters['cabin'])) {
                error_log("  ❌ Cabin filter: {$filters['cabin']} exceeds available cabins {$data['cabins']}");
                continue;
            }

            error_log("  ✅ All filters passed for YachtID {$yacht_id}");

            // Merge offer metadata into the local boat data under 'offer' key
            $data['offer'] = $offer;

            // Provide a simple availability_year if API provides dateFrom
            if (!empty($offer['dateFrom'])) {
                $ts = strtotime($offer['dateFrom']);
                if ($ts !== false) {
                    $data['availability_year'] = date('Y', $ts);
                }
            }

            $boats[] = $data;
        }

        error_log("🔍 Boat Processing Results:");
        error_log("  - Offers from API: " . count($offers));
        error_log("  - Boats matched in DB: " . count($boats));
        error_log("  - Per page setting: " . $per_page);
        error_log("  - Current page: " . $paged);

        // Pagination: simple array-based pagination
        $total = count($boats);
        $pages = ($per_page > 0) ? (int) ceil($total / $per_page) : 1;
        $paged = max(1, intval($paged));
        $offset = ($paged - 1) * $per_page;
        $paged_boats = array_slice($boats, $offset, $per_page);

        error_log("🔍 Pagination Calculations:");
        error_log("  - Total boats: " . $total);
        error_log("  - Total pages: " . $pages);
        error_log("  - Offset: " . $offset);
        error_log("  - Boats on this page: " . count($paged_boats));

        // If no boats matched (API returned offers but none matched local DB),
        // try local-only search as fallback
        if (empty($boats) && !empty($offers)) {
            error_log("🔄 API returned offers but no local matches. Trying local-only search...");
            $local_boats = $this->search_local_boats($filters, $per_page, $paged);
            if (!empty($local_boats['boats'])) {
                error_log("✅ Local-only search found " . count($local_boats['boats']) . " boats");
                return $local_boats;
            }
        }

        return [
            'boats'     => $paged_boats,
            'paged'     => $paged,
            'pages'     => $pages,
            'per_page'  => $per_page,
            'total'     => $total
        ];

    }

    public function fetch_boat_offers($country, $productName, $dateFrom, $dateTo, $filters = [])
    {
        // Build query parameters - start with essentials only
        $params = [];

        // Core required parameters
        if (!empty($country)) {
            $params['country'] = $country;
        }

        if (!empty($productName)) {
            $params['productName'] = $productName;
        }

        if (!empty($dateFrom)) {
            $params['dateFrom'] = $dateFrom;
        }

        if (!empty($dateTo)) {
            $params['dateTo'] = $dateTo;
        }

        // Calculate trip duration from dates if available
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

        // Only add optional parameters if they have meaningful values from user input
        if (!empty($filters['person']) && intval($filters['person']) > 0) {
            $params['passengersOnBoard'] = intval($filters['person']);
        }

        if (!empty($filters['cabin']) && intval($filters['cabin']) > 0) {
            $params['minCabins'] = intval($filters['cabin']);
        }

        if (!empty($filters['year']) && intval($filters['year']) > 1900) {
            $year = intval($filters['year']);
            $params['minYearOfBuild'] = $year;
            $params['maxYearOfBuild'] = $year;
        }

        // Map charter types to API kind parameter (only if specified)
        if (!empty($filters['charter_type'])) {
            $kind_mapping = [
                'Sail boat' => 'sailboat',
                'Motor boat' => 'motorboat',
                'Catamaran' => 'catamaran',
                'Motor yacht' => 'motoryacht',
                'Power' => 'motorboat',
                'Sailing yacht' => 'sailboat'
            ];

            $mapped_kind = $kind_mapping[$filters['charter_type']] ?? strtolower(str_replace(' ', '', $filters['charter_type']));
            if ($mapped_kind) {
                $params['kind'] = $mapped_kind;
            }
        }

        // Default parameters for better API results
        $params['currency'] = 'EUR';
        $params['showOptions'] = 'true';

        // Handle flexibility parameter (1 = exact dates by default)
        if (isset($filters['flexibility']) && !empty($filters['flexibility'])) {
            $flexibility = intval($filters['flexibility']);
            if ($flexibility >= 1 && $flexibility <= 7) {
                $params['flexibility'] = $flexibility;
            }
        } else {
            $params['flexibility'] = 1; // Default to exact dates
        }

        // Add advanced filtering parameters if they exist
        if (!empty($filters['berths']) && intval($filters['berths']) > 0) {
            $params['minBerths'] = intval($filters['berths']);
        }

        if (!empty($filters['wc']) && intval($filters['wc']) > 0) {
            $params['minHeads'] = intval($filters['wc']);
        }

        if (!empty($filters['min_length']) && floatval($filters['min_length']) > 0) {
            $params['minLength'] = floatval($filters['min_length']);
        }

        if (!empty($filters['max_length']) && floatval($filters['max_length']) > 0) {
            $params['maxLength'] = floatval($filters['max_length']);
        }

        if (!empty($filters['company_id']) && intval($filters['company_id']) > 0) {
            $params['companyId'] = intval($filters['company_id']);
        }

        if (!empty($filters['base_from_id']) && intval($filters['base_from_id']) > 0) {
            $params['baseFromId'] = intval($filters['base_from_id']);
        }

        if (!empty($filters['base_to_id']) && intval($filters['base_to_id']) > 0) {
            $params['baseToId'] = intval($filters['base_to_id']);
        }

        if (!empty($filters['sailing_area_id']) && intval($filters['sailing_area_id']) > 0) {
            $params['sailingAreaId'] = intval($filters['sailing_area_id']);
        }

        if (!empty($filters['model_id']) && intval($filters['model_id']) > 0) {
            $params['modelId'] = intval($filters['model_id']);
        }

        // Handle specific yacht ID filtering (for single boat price lookups)
        if (!empty($filters['yachtId'])) {
            if (is_array($filters['yachtId'])) {
                // Multiple yacht IDs
                $params['yachtId'] = implode(',', array_map('intval', $filters['yachtId']));
            } else {
                // Single yacht ID
                $params['yachtId'] = intval($filters['yachtId']);
            }
        }

        // Remove any null or empty values to keep URL clean
        $params = array_filter($params, function($value) {
            return $value !== null && $value !== '';
        });

        // Build clean API URL
        $query = http_build_query($params);
        $api_url = "https://www.booking-manager.com/api/v2/offers?$query";

        error_log("🌐 Clean API URL: " . $api_url);
        error_log("🔍 Essential parameters only (count: " . count($params) . "):");
        foreach($params as $key => $value) {
            error_log("  - {$key}: {$value}");
        }

        // Log parameter source
        if (count($params) <= 5) {
            error_log("✅ Minimal API call - using essential parameters only");
        } else {
            error_log("🔧 Enhanced API call - additional filters applied");
        }

        // Log popular testing combinations for debugging
        $popular_countries = ['GR', 'HR', 'TR', 'IT', 'ES', 'FR'];
        if (!empty($country) && !in_array($country, $popular_countries)) {
            error_log("💡 Testing with less common country: {$country}. Popular alternatives: GR, HR, TR, IT");
        }

        $ch = curl_init($api_url);

        // Get API key for debugging
        $api_key = $this->cread()['api_key'];
        error_log("🔑 API Key exists: " . (!empty($api_key) ? 'Yes' : 'No'));
        if (!empty($api_key)) {
            error_log("🔑 API Key length: " . strlen($api_key));
        }

        $start_time = microtime(true);
        error_log("⏱️ Starting API call at: " . date('H:i:s'));

        curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                        "accept: application/json",
                        "Authorization: Bearer " . $this->cread()['api_key'],
                        "Connection: keep-alive", // Keep connection alive for better performance
                        "User-Agent: WordPress-Boat-Listing-Plugin/1.0",
                ],
                CURLOPT_TIMEOUT => 60, // Increased to 60 seconds for slower API responses
                CURLOPT_CONNECTTIMEOUT => 15, // Increased connection timeout
                CURLOPT_ENCODING => 'gzip, deflate',
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_TCP_KEEPALIVE => 1, // Enable TCP keep-alive
                CURLOPT_TCP_KEEPIDLE => 30, // Keep connection idle for 30 seconds
                CURLOPT_TCP_KEEPINTVL => 10, // Interval between keep-alive probes
                CURLOPT_NOSIGNAL => 1, // Prevent timeout issues in multi-threaded environment
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $total_time = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $connect_time = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
        curl_close($ch);

        $end_time = microtime(true);
        $execution_time = round(($end_time - $start_time), 2);

        error_log("⏱️ API call completed in: " . $execution_time . "s");
        error_log("🔗 HTTP Code: " . $http_code);
        error_log("⏱️ CURL Total Time: " . round($total_time, 2) . "s");
        error_log("⏱️ CURL Connect Time: " . round($connect_time, 2) . "s");

        if ($curl_error) {
            error_log("❌ CURL Error: " . $curl_error);

            // Log specific error types
            if (strpos($curl_error, 'timeout') !== false) {
                error_log("⏰ TIMEOUT ERROR - API took longer than 60 seconds to respond");
            } elseif (strpos($curl_error, 'connect') !== false) {
                error_log("🔌 CONNECTION ERROR - Could not connect to API server");
            } elseif (strpos($curl_error, 'resolve') !== false) {
                error_log("🌐 DNS ERROR - Could not resolve API hostname");
            }
        }

        // Log response details
        error_log("📨 Raw Response length: " . strlen($response));
        error_log("📨 Raw Response (first 500 chars): " . substr($response, 0, 500));

        // Check for common API errors
        if ($http_code === 401) {
            error_log("❌ API Authentication Error - Check API key");
        } elseif ($http_code === 404) {
            error_log("❌ API Endpoint Not Found");
        } elseif ($http_code !== 200) {
            error_log("❌ API Error - HTTP " . $http_code);
        }

        if ($response === false) {
            error_log("❌ API call failed");
            return [];
        }

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("❌ JSON decode error: " . json_last_error_msg());
            return [];
        }

        error_log("✅ API call successful, returning data");
        return $data;
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