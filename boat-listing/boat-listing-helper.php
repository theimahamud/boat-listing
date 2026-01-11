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


    /**
     * Cached version of fetch_boat_offers
     * Only uses cache if EXACT same filters are applied
     */
    public function fetch_boat_offers_cached($country, $productName, $dateFrom, $dateTo, $filters = []) {

        // Build a unique cache key based on ALL filter parameters
        $cache_params = [
                'country' => $country,
                'productName' => $productName,
                'dateFrom' => $dateFrom,
                'dateTo' => $dateTo,
                'filters' => $filters
        ];

        // Sort filters array to ensure consistent cache keys
        if (is_array($filters)) {
            ksort($filters);
        }

        // Create unique hash from all parameters
        $cache_key = 'bl_offers_' . md5(serialize($cache_params));

        error_log("🔍 Cache Key: " . $cache_key);
        error_log("🔍 Cache Params: " . json_encode($cache_params));

        // Try to get from cache
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            error_log("✅ CACHE HIT! Using cached offers (exact same filters)");
            error_log("📊 Cached offers count: " . count($cached_data));
            return $cached_data;
        }

        error_log("❌ CACHE MISS - Making fresh API call");

        // Not in cache or different filters - make API call
        $offers = $this->fetch_boat_offers($country, $productName, $dateFrom, $dateTo, $filters);

        // Only cache if we got valid results
        if (!empty($offers) && is_array($offers)) {
            // Cache for 15 minutes (900 seconds)
            set_transient($cache_key, $offers, 15 * MINUTE_IN_SECONDS);
            error_log("💾 Cached " . count($offers) . " offers for 15 minutes");
        } else {
            error_log("⚠️ Not caching - empty or invalid API response");
        }

        return $offers;
    }

    /**
     * Updated boat_filters to use cached version
     * Replace the fetch_boat_offers call with this
     */
    public function boat_filters($country, $productName, $dateFrom, $dateTo, $paged = 1, $per_page = 10, $filters = []): array
    {
        global $wpdb;

        error_log("🔍 ========== BOAT_FILTERS DEBUG START ==========");
        error_log("🔍 boat_filters called with:");
        error_log("  - country: '" . $country . "'");
        error_log("  - productName: '" . $productName . "'");
        error_log("  - dateFrom: '" . $dateFrom . "'");
        error_log("  - dateTo: '" . $dateTo . "'");
        error_log("  - paged: " . $paged);
        error_log("  - per_page: " . $per_page);
        error_log("🔧 Local filters:");
        foreach($filters as $key => $value) {
            if (!empty($value)) {
                error_log("  - {$key}: {$value}");
            }
        }

        // Check if we have date filters
        $hasDateFilters = !empty($dateFrom) && !empty($dateTo);
        $hasLocalFilters = !empty($filters['person']) || !empty($filters['cabin']) || !empty($filters['charter_type']) || !empty($filters['year']);

        if (!$hasDateFilters) {
            error_log("⚠️ No dates provided - using local-only search");
            return $this->search_local_boats($filters, $per_page, $paged);
        }

        // STEP 1: Fetch offers from API WITH CACHING - THIS IS OUR SOURCE OF TRUTH
        error_log("📡 Fetching offers from API (with smart caching)...");

        // Use cached version - only uses cache if EXACT same filters
        $offers = $this->fetch_boat_offers_cached($country, $productName, $dateFrom, $dateTo, $filters);

        if (empty($offers) || !is_array($offers)) {
            error_log("❌ API returned no offers");
            return [
                    'boats'     => [],
                    'paged'     => $paged,
                    'pages'     => 0,
                    'per_page'  => $per_page,
                    'total'     => 0
            ];
        }

        error_log("✅ Got " . count($offers) . " offers (cached or fresh API call)");

        // STEP 2: Extract unique yacht IDs from API offers - THESE ARE THE ONLY BOATS WE CAN SHOW
        $yacht_ids_from_api = array_unique(array_filter(array_map(function($offer) {
            return isset($offer['yachtId']) ? intval($offer['yachtId']) : 0;
        }, $offers)));

        error_log("📊 Found " . count($yacht_ids_from_api) . " unique yacht IDs from API offers");

        // STEP 3: Batch fetch ONLY these specific boats from local database
        $boats_map = $this->fetch_boats_by_ids($yacht_ids_from_api);
        error_log("📊 Found " . count($boats_map) . " matching boats in local database");

        // STEP 4: Match offers with local boat data and apply LOCAL filters
        $boats = [];
        $filter_stats = [
                'api_offers' => count($offers),
                'unique_yacht_ids' => count($yacht_ids_from_api),
                'matched_in_db' => 0,
                'filtered_by_charter_type' => 0,
                'filtered_by_person' => 0,
                'filtered_by_year' => 0,
                'filtered_by_cabin' => 0,
                'passed_all_filters' => 0
        ];

        foreach ($offers as $offer) {
            $yacht_id = isset($offer['yachtId']) ? intval($offer['yachtId']) : 0;

            if (!$yacht_id || !isset($boats_map[$yacht_id])) {
                continue;
            }

            $filter_stats['matched_in_db']++;
            $data = $boats_map[$yacht_id];
            $boat_name = $data['name'] ?? 'Unknown';

            // Apply LOCAL filters to reduce the API results
            $passed_all_filters = true;

            // Charter type filter
            if (!empty($filters['charter_type']) && !empty($data['kind'])) {
                if (stripos($data['kind'], $filters['charter_type']) === false) {
                    $filter_stats['filtered_by_charter_type']++;
                    $passed_all_filters = false;
                    continue;
                }
            }

            // Person filter
            if (!empty($filters['person'])) {
                $requested_person = intval($filters['person']);
                $boat_max_people = intval($data['maxPeopleOnBoard'] ?? 0);

                if ($boat_max_people != $requested_person) {
                    $filter_stats['filtered_by_person']++;
                    $passed_all_filters = false;
                    continue;
                }
            }

            // Year filter
            if (!empty($filters['year'])) {
                $requested_year = intval($filters['year']);
                $boat_year = intval($data['year'] ?? 0);

                if ($boat_year != $requested_year) {
                    $filter_stats['filtered_by_year']++;
                    $passed_all_filters = false;
                    continue;
                }
            }

            // Cabin filter
            if (!empty($filters['cabin'])) {
                $requested_cabins = intval($filters['cabin']);
                $boat_cabins = intval($data['cabins'] ?? 0);

                if ($boat_cabins != $requested_cabins) {
                    $filter_stats['filtered_by_cabin']++;
                    $passed_all_filters = false;
                    continue;
                }
            }

            // If passed all filters, add to results with offer data
            if ($passed_all_filters) {
                $filter_stats['passed_all_filters']++;

                // Merge offer data with boat data
                $data['offer'] = $offer;

                // Add availability year if available
                if (!empty($offer['dateFrom'])) {
                    $ts = strtotime($offer['dateFrom']);
                    if ($ts !== false) {
                        $data['availability_year'] = date('Y', $ts);
                    }
                }

                $boats[] = $data;
            }
        }

        error_log("📊 Filtering Statistics:");
        error_log("  - API offers received: " . $filter_stats['api_offers']);
        error_log("  - Unique yacht IDs from API: " . $filter_stats['unique_yacht_ids']);
        error_log("  - Matched in local DB: " . $filter_stats['matched_in_db']);
        error_log("  - Filtered by charter_type: " . $filter_stats['filtered_by_charter_type']);
        error_log("  - Filtered by person: " . $filter_stats['filtered_by_person']);
        error_log("  - Filtered by year: " . $filter_stats['filtered_by_year']);
        error_log("  - Filtered by cabin: " . $filter_stats['filtered_by_cabin']);
        error_log("  - ✅ PASSED ALL FILTERS: " . $filter_stats['passed_all_filters']);

        // STEP 5: Pagination
        $total = count($boats);
        $pages = ($per_page > 0) ? (int) ceil($total / $per_page) : 1;
        $paged = max(1, intval($paged));
        $offset = ($paged - 1) * $per_page;
        $paged_boats = array_slice($boats, $offset, $per_page);

        error_log("🔍 ========== FINAL RESULTS ==========");
        error_log("📊 Total boats AFTER filtering: " . $total . " (started with " . count($offers) . " API offers)");
        error_log("📊 Boats on current page: " . count($paged_boats));
        error_log("📊 Total pages: " . $pages);
        error_log("🔍 ========== BOAT_FILTERS DEBUG END ==========");

        return [
                'boats'     => $paged_boats,
                'paged'     => $paged,
                'pages'     => $pages,
                'per_page'  => $per_page,
                'total'     => $total,
                'has_prices' => true
        ];
    }

    /**
     * Optional: Clear cache manually if needed
     */
    public function clear_offers_cache() {
        global $wpdb;

        // Delete all offer cache transients
        $wpdb->query(
                "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_bl_offers_%' 
             OR option_name LIKE '_transient_timeout_bl_offers_%'"
        );

        error_log("🗑️ Cleared all offers cache");

        return true;
    }

    /**
     * Optional: Get cache statistics
     */
    public function get_cache_stats() {
        global $wpdb;

        $cache_count = $wpdb->get_var(
                "SELECT COUNT(*) FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_bl_offers_%'"
        );

        return [
                'cached_searches' => $cache_count,
                'cache_duration' => '15 minutes'
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

        // Special handling: If no country or productName provided but we have dates and local filters,
        // we need to make a broader API call to get available boats
        $hasMainFilters = !empty($country) || !empty($productName);
        $hasDateFilters = !empty($dateFrom) && !empty($dateTo);
        $hasLocalFilters = !empty($filters['person']) || !empty($filters['cabin']) || !empty($filters['charter_type']);

        if (!$hasMainFilters && $hasDateFilters && $hasLocalFilters) {
            error_log("🔍 No main filters (country/product) but have date + local filters. Making broader API call.");
            // The API might require at least a country to return meaningful results
            // For broader search, let's try without country/product restrictions
            // If this doesn't work, we may need to use a default popular country like 'GR'
        }

        // Fallback: If we have dates and local filters but no API filters, and the API returns empty,
        // we might need to add a default country. This would be handled after the API call fails.

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

        // NOTE: Person filter removed - will be applied locally to database results
        // if (!empty($filters['person']) && intval($filters['person']) > 0) {
        //     $params['passengersOnBoard'] = intval($filters['person']);
        // }

        // NOTE: Cabin filter removed - will be applied locally to database results
        // if (!empty($filters['cabin']) && intval($filters['cabin']) > 0) {
        //     $params['minCabins'] = intval($filters['cabin']);
        // }

        // NOTE: Year filter removed - will be applied locally to database results
        // if (!empty($filters['year']) && intval($filters['year']) > 1900) {
        //     $year = intval($filters['year']);
        //     $params['minYearOfBuild'] = $year;
        //     $params['maxYearOfBuild'] = $year;
        // }

        // NOTE: Charter type filter removed - will be applied locally to database results
        // Map charter types to API kind parameter (only if specified)
        // if (!empty($filters['charter_type'])) {
        //     $kind_mapping = [
        //             'Sail boat' => 'sailboat',
        //             'Motor boat' => 'motorboat',
        //             'Catamaran' => 'catamaran',
        //             'Motor yacht' => 'motoryacht',
        //             'Power' => 'motorboat',
        //             'Sailing yacht' => 'sailboat'
        //     ];
        //
        //     $mapped_kind = $kind_mapping[$filters['charter_type']] ?? strtolower(str_replace(' ', '', $filters['charter_type']));
        //     if ($mapped_kind) {
        //         $params['kind'] = $mapped_kind;
        //     }
        // }

        // Default parameters for better API results
        $params['currency'] = $filters['currency'] ?? 'EUR';
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

        // Only add API-specific filters, not local database filters
        // Local filters (person, cabin, year, berths, wc, charterType) will be applied to database results

        // API-specific optional parameters (these can help narrow down API results)
        if (!empty($filters['companyId'])) $params['companyId'] = intval($filters['companyId']);
        if (!empty($filters['baseFromId'])) $params['baseFromId'] = intval($filters['baseFromId']);
        if (!empty($filters['baseToId'])) $params['baseToId'] = intval($filters['baseToId']);
        if (!empty($filters['sailingAreaId'])) $params['sailingAreaId'] = intval($filters['sailingAreaId']);
        if (!empty($filters['modelId'])) $params['modelId'] = intval($filters['modelId']);
        if (!empty($filters['yachtId'])) {
            // Handle yachtId - can be array or single value
            if (is_array($filters['yachtId'])) {
                $params['yachtId'] = implode(',', array_map('intval', $filters['yachtId']));
            } else {
                $params['yachtId'] = intval($filters['yachtId']);
            }
        }
        if (!empty($filters['min_length'])) $params['minLength'] = floatval($filters['min_length']);
        if (!empty($filters['max_length'])) $params['maxLength'] = floatval($filters['max_length']);

        // Remove any null or empty values to keep URL clean
        $params = array_filter($params, function($value) {
            return $value !== null && $value !== '';
        });

        // Build clean API URL
        $query = http_build_query($params);
        $api_url = "https://www.booking-manager.com/api/v2/offers?$query";

        $ch = curl_init($api_url);

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
            error_log("📄 Raw response: " . substr($response, 0, 1000));
            return [];
        }

        // Enhanced debugging for API responses
        if (empty($data) || !is_array($data)) {
            error_log("⚠️ API returned empty or invalid data");
            error_log("🔍 Request params were: " . json_encode($params));
            error_log("🌐 API URL was: " . $api_url);
            error_log("📊 HTTP Code: " . $http_code);
            error_log("📄 Response type: " . gettype($data));
            error_log("📄 Response content: " . substr($response, 0, 500));
            return [];
        }

        error_log("✅ API call successful, returning " . count($data) . " offers");
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

        error_log("🔍 ========== SEARCH_LOCAL_BOATS DEBUG START ==========");
        error_log("🔍 Searching local database with filters:");
        foreach($filters as $key => $value) {
            if (!empty($value)) {
                error_log("  - {$key}: {$value}");
            }
        }

        // Get all boats from database
        $all_boats = $this->fetch_all_boats();
        error_log("📊 Total boats in database: " . count($all_boats));
        
        $filtered_boats = [];
        $filter_stats = [
            'total' => count($all_boats),
            'charter_type_filtered' => 0,
            'person_filtered' => 0,
            'year_filtered' => 0,
            'cabin_filtered' => 0,
            'passed_all_filters' => 0
        ];

        foreach ($all_boats as $boat_row) {
            $data = $boat_row['data'];
            $boat_name = $data['name'] ?? 'Unknown';
            $passed_filters = true;

            // Charter type filter - Check if kind contains requested type
            if (!empty($filters['charter_type']) && !empty($data['kind'])) {
                if (stripos($data['kind'], $filters['charter_type']) === false) {
                    error_log("❌ Boat '{$boat_name}' filtered out by charter_type: '{$data['kind']}' doesn't contain '{$filters['charter_type']}'");
                    $filter_stats['charter_type_filtered']++;
                    $passed_filters = false;
                    continue;
                }
            }

            // Person filter - Check maxPeopleOnBoard == requested (exact equality)
            if (!empty($filters['person'])) {
                $requested_person = intval($filters['person']);
                $boat_max_people = intval($data['maxPeopleOnBoard'] ?? 0);
                
                error_log("👥 Person filter check: Boat '{$boat_name}' max people: {$boat_max_people}, requested: {$requested_person}");
                
                if (empty($data['maxPeopleOnBoard'])) {
                    error_log("⚠️ Boat '{$boat_name}' has no maxPeopleOnBoard data - skipping person filter");
                } else if ($boat_max_people != $requested_person) {
                    error_log("❌ Boat '{$boat_name}' filtered out by person: max people ({$boat_max_people}) != requested ({$requested_person})");
                    $filter_stats['person_filtered']++;
                    $passed_filters = false;
                    continue;
                } else {
                    error_log("✅ Boat '{$boat_name}' passes person filter: max people ({$boat_max_people}) == requested ({$requested_person})");
                }
            }

            // Year filter - Check year == requested (exact equality)
            if (!empty($filters['year']) && !empty($data['year'])) {
                $requested_year = intval($filters['year']);
                $boat_year = intval($data['year']);

                if ($boat_year != $requested_year) {
                    error_log("❌ Boat '{$boat_name}' filtered out by year: {$boat_year} != {$requested_year}");
                    $filter_stats['year_filtered']++;
                    $passed_filters = false;
                    continue;
                }
            }

            // Cabin filter - Check cabins == requested (exact equality)
            if (!empty($filters['cabin']) && !empty($data['cabins'])) {
                $requested_cabins = intval($filters['cabin']);
                $boat_cabins = intval($data['cabins']);

                if ($boat_cabins != $requested_cabins) {
                    error_log("❌ Boat '{$boat_name}' filtered out by cabin: {$boat_cabins} != {$requested_cabins}");
                    $filter_stats['cabin_filtered']++;
                    $passed_filters = false;
                    continue;
                }
            }

            if ($passed_filters) {
                error_log("✅ Boat '{$boat_name}' passed all filters - adding to results");
                $filter_stats['passed_all_filters']++;
                $filtered_boats[] = $data;
            }
        }

        error_log("📊 Filter Statistics:");
        error_log("  - Total boats: " . $filter_stats['total']);
        error_log("  - Filtered by charter_type: " . $filter_stats['charter_type_filtered']);
        error_log("  - Filtered by person: " . $filter_stats['person_filtered']);
        error_log("  - Filtered by year: " . $filter_stats['year_filtered']);
        error_log("  - Filtered by cabin: " . $filter_stats['cabin_filtered']);
        error_log("  - Passed all filters: " . $filter_stats['passed_all_filters']);

        // Pagination
        $total = count($filtered_boats);
        $pages = ($per_page > 0) ? (int) ceil($total / $per_page) : 1;
        $paged = max(1, intval($paged));
        $offset = ($paged - 1) * $per_page;
        $paged_boats = array_slice($filtered_boats, $offset, $per_page);

        error_log("📊 Pagination Results:");
        error_log("  - Total filtered boats: " . $total);
        error_log("  - Total pages: " . $pages);
        error_log("  - Current page: " . $paged);
        error_log("  - Boats on current page: " . count($paged_boats));
        error_log("🔍 ========== SEARCH_LOCAL_BOATS DEBUG END ==========");

        return [
                'boats' => $paged_boats,
                'paged' => $paged,
                'pages' => $pages,
                'per_page' => $per_page,
                'total' => $total
        ];
    }

    public function get_single_yacht_offer_details_cached($boat_id, $date_from, $date_to, $filters = [])
    {
        // Build cache key from all parameters
        $cache_params = [
                'boat_id' => $boat_id,
                'date_from' => $date_from,
                'date_to' => $date_to,
                'filters' => $filters
        ];

        // Sort filters for consistent cache keys
        if (is_array($filters)) {
            ksort($filters);
        }

        $cache_key = 'bl_yacht_price_' . md5(serialize($cache_params));

        error_log("🔍 Details Cache Key: " . $cache_key);

        // Try to get from cache
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            error_log("✅ CACHE HIT! Using cached price details for yacht {$boat_id}");
            return $cached_data;
        }

        error_log("❌ CACHE MISS - Fetching fresh price details for yacht {$boat_id}");

        // Not in cache - fetch fresh data
        $price_details = $this->get_single_yacht_offer_details($boat_id, $date_from, $date_to, $filters);

        // Only cache if we got valid results
        if (!empty($price_details) && isset($price_details['rows'])) {
            // Cache for 30 minutes (longer than filter page since details change less often)
            set_transient($cache_key, $price_details, 30 * MINUTE_IN_SECONDS);
            error_log("💾 Cached price details for yacht {$boat_id} for 30 minutes");
        }

        return $price_details;
    }

    /**
     * OPTION 2: Cache boat data separately (rarely changes)
     * This caches the boat information itself (not prices)
     */
    public function fetch_boat_data_cached($boat_id)
    {
        $cache_key = 'bl_boat_data_' . $boat_id;

        // Try to get from cache
        $cached_data = get_transient($cache_key);

        if ($cached_data !== false) {
            error_log("✅ CACHE HIT! Using cached boat data for {$boat_id}");
            return $cached_data;
        }

        error_log("❌ CACHE MISS - Fetching fresh boat data for {$boat_id}");

        // Fetch from database
        $boat_data = $this->fetch_all_boats($boat_id);

        if (!empty($boat_data)) {
            // Cache for 24 hours (boat data rarely changes)
            set_transient($cache_key, $boat_data, 24 * HOUR_IN_SECONDS);
            error_log("💾 Cached boat data for {$boat_id} for 24 hours");
        }

        return $boat_data;
    }

    /**
     * Updated get_single_yacht_offer_details with better API handling
     * (Use this as the non-cached version)
     */
    public function get_single_yacht_offer_details($boat_id, $date_from, $date_to, $filters = [])
    {
        if (empty($date_from) || empty($date_to)) {
            error_log("⚠️ No dates provided for price details");
            return [
                    'min' => 'N/A',
                    'max' => 'N/A',
                    'currency' => 'EUR',
                    'rows' => []
            ];
        }

        try {
            error_log("📡 Getting price details for yacht ID: {$boat_id}");

            $country = $filters['country'] ?? '';
            $productName = $filters['productName'] ?? '';

            // Remove yachtId from API filters (API works better without it)
            $api_filters = $filters;
            unset($api_filters['yachtId']);

            // Call the API - use cached version if available
            $offers = $this->fetch_boat_offers_cached($country, $productName, $date_from, $date_to, $api_filters);

            if (empty($offers)) {
                // Fallback
                error_log("🔄 Trying fallback API call");
                $offers = $this->fetch_boat_offers_cached('', '', $date_from, $date_to, []);
            }

            if (empty($offers)) {
                error_log("❌ No offers available");
                return [
                        'min' => 'N/A',
                        'max' => 'N/A',
                        'currency' => 'EUR',
                        'rows' => []
                ];
            }

            // Filter for our specific yacht
            $prices = [];
            $currency = 'EUR';

            foreach ($offers as $offer) {
                if (isset($offer['yachtId']) && $offer['yachtId'] == $boat_id) {

                    $starting_price = floatval($offer['startPrice'] ?? $offer['price'] ?? 0);

                    $price_info = [
                            'dateFrom' => $offer['dateFrom'] ?? '',
                            'dateTo' => $offer['dateTo'] ?? '',
                            'startPrice' => $starting_price,
                            'price' => $starting_price,
                            'currency' => $offer['currency'] ?? 'EUR',
                            'securityDeposit' => floatval($offer['securityDeposit'] ?? 0),
                            'startBase' => $offer['startBase'] ?? '',
                            'endBase' => $offer['endBase'] ?? '',
                    ];

                    $prices[] = $price_info;
                    $currency = $offer['currency'] ?? 'EUR';
                }
            }

            if (empty($prices)) {
                return [
                        'min' => 'N/A',
                        'max' => 'N/A',
                        'currency' => $currency,
                        'rows' => []
                ];
            }

            $all_starting_prices = array_column($prices, 'startPrice');
            $min_price = min($all_starting_prices);
            $max_price = max($all_starting_prices);

            return [
                    'min' => number_format($min_price, 2),
                    'max' => number_format($max_price, 2),
                    'currency' => $currency,
                    'rows' => $prices
            ];

        } catch (Exception $e) {
            error_log("❌ Error: " . $e->getMessage());
            return [
                    'min' => 'N/A',
                    'max' => 'N/A',
                    'currency' => 'EUR',
                    'rows' => []
            ];
        }
    }
    /**
     * Efficiently fetch boats by yacht IDs from API offers
     * This avoids N+1 query problem by batching database queries
     */
    private function fetch_boats_by_ids($yacht_ids) {
        global $wpdb;

        if (empty($yacht_ids)) {
            return [];
        }

        $boats_table = $wpdb->prefix . 'boats';

        // Create placeholders for prepared statement
        $placeholders = implode(',', array_fill(0, count($yacht_ids), '%d'));
        $query = "SELECT id, data FROM {$boats_table} WHERE id IN ($placeholders)";

        error_log("🔍 Batch fetching " . count($yacht_ids) . " boats from database");

        $results = $wpdb->get_results($wpdb->prepare($query, ...$yacht_ids), ARRAY_A);

        $boats_map = [];
        foreach ($results as $row) {
            $boat_data = json_decode($row['data'], true);
            if (is_array($boat_data)) {
                $boats_map[intval($row['id'])] = $boat_data;
            }
        }

        error_log("🔍 Successfully loaded " . count($boats_map) . " boats from database");

        return $boats_map;
    }
}

new Boat_Listing_Helper();

