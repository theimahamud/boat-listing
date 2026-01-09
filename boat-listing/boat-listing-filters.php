<?php

    add_shortcode('boat_filter', 'render_boat_listing_filter');

    function render_boat_listing_filter( $atts, $content = null){

        $helper = new Boat_Listing_Helper();
        $charterTypes = $helper->fetch_category();
        $preloader = $helper->bl_render_boat_overlay_preloader();
        $boat_data = $helper->fetch_all_boats();
        $icon = $helper->icons();
        $countries = $helper->fetch_country();
        $regions = $helper->fetch_regions();
        $getAvailableYachts = $helper->getAvailableYachts();

        // Find Caribbean region first
        $firstRegion = null;
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


        ob_start();

        ?>

            <div class="boat-listing-filter-area" style="position:relative">

                <div class="boat-lists-loader">
                    <?php echo $preloader; ?>
                </div>

                <div class="boat-listing-filter-wraper">

                    <div class="filter-bar-area">
                        <div class="filter-bar">
                            <strong><?php echo $icon['filter']; ?> Filter By</strong>

                            <div class="filter-fieldset">
                                <label for="boat_charter_type"><?php echo $icon['charter_type']; ?> Charter Type</label>
                                <select name="boat_charter_type" id="boat_charter_type" class="boat-listing-select2">
                                    <option value="">Select Charter Type </option>
                                    <?php foreach ($charterTypes as $category): ?>
                                        <option value="<?php echo esc_attr($category['name']); ?>">
                                            <?php echo esc_html($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Date filter -->
                            <div class="filter-fieldset">
                                <label for="search_free_yacht"><?php echo $icon['date']; ?> Dates </label>
                                <input type="text" id="search_free_yacht" name="search_free_yacht" placeholder="Select date range" autocomplete="off" class="boat-listing-input-text bl-date-range-picker" />
                            </div>

                             <!-- Regions -->
                            <div class="filter-fieldset">
                                <label for="boat_location">
                                    <?php echo $icon['location']; ?> Destinations
                                </label>

                                <select name="boat_location" id="boat_location" class="boat-listing-select2">
                                    <option value="">All Destinations</option>

                                    <?php
                                    /**
                                     * Priority destinations
                                     */
                                    $priorityNames = [
                                            // 'British Virgin Islands',
                                            // 'United States Virgin Islands',
                                    ];

                                    $priorityCountries = [];
                                    $remainingCountries = [];

                                    // Separate priority countries from the rest
                                    foreach ($countries as $country) {
                                        $countryName = $country['country_data']['name'] ?? '';

                                        if (in_array($countryName, $priorityNames, true)) {
                                            $priorityCountries[] = $country;
                                        } else {
                                            $remainingCountries[] = $country;
                                        }
                                    }
                                    ?>

                                    <?php if (!empty($priorityCountries)): ?>
                                        <optgroup label="Popular Destinations">
                                            <?php foreach ($priorityCountries as $country): ?>
                                                <option value="<?php echo esc_attr($country['id']); ?>">
                                                    <?php echo esc_html($country['country_data']['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endif; ?>

                                    <?php
                                    /**
                                     * Render remaining countries by region
                                     */
                                    foreach ($regionsOrdered as $region):
                                        $regionId   = $region['id'];
                                        $regionName = $region['regions_data']['name'] ?? 'Unknown Region';

                                        $regionCountries = array_filter($remainingCountries, function ($country) use ($regionId) {
                                            return ($country['country_data']['worldRegion'] ?? null) == $regionId;
                                        });

                                        if (!empty($regionCountries)):
                                            ?>
                                            <optgroup label="<?php echo esc_html($regionName); ?>">
                                                <?php foreach ($regionCountries as $country): ?>
                                                    <option value="<?php echo esc_attr($country['id']); ?>">
                                                        − <?php echo esc_html($country['country_data']['name']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </optgroup>
                                        <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </select>
                            </div>

                            <div class="filter-fieldset">
                                <label for="boat_category">
                                    <?php echo $icon['category']; ?> Category
                                </label>

                                <select name="boat_category" id="boat_category" class="boat-listing-select2">
                                    <option value="">Select Category</option>

                                    <?php
                                    // Collect unique product names
                                    $category_list = [];

                                    foreach ($getAvailableYachts as $yacht) {
                                        $data = json_decode($yacht['data'], true);

                                        if (!empty($data['products']) && is_array($data['products'])) {
                                            foreach ($data['products'] as $product) {
                                                if (!empty($product['name'])) {
                                                    $category_list[] = trim($product['name']);
                                                }
                                            }
                                        }
                                    }

                                    // Make unique & sort
                                    $category_list = array_unique($category_list);
                                    sort($category_list, SORT_STRING);

                                    function formatCategoryName($string) {
                                        // Add space before capital letters
                                        $string = preg_replace('/(?<!^)([A-Z])/', ' $1', $string);
                                        return trim($string);
                                    }

                                    foreach ($category_list as $category):
                                        $formatted = formatCategoryName($category);
                                        ?>
                                        <option value="<?php echo esc_attr($category); ?>">
                                            <?php echo esc_html($formatted); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!--Person filter -->
                            <div class="filter-fieldset">
                                <label for="boat_person"><?php echo $icon['person']; ?> Person</label>
                                <select name="boat_person" id="boat_person" class="boat-listing-select2">
                                    <option value="">Select Person</option>
                                    <?php
                                    $person_list = [];

                                    foreach ($getAvailableYachts as $yacht) {
                                        $p_data = json_decode($yacht['data'], true);

                                        if (!empty($p_data['maxPeopleOnBoard']) && (int)$p_data['maxPeopleOnBoard'] > 0) {
                                            $person_list[] = (int)$p_data['maxPeopleOnBoard'];
                                        }
                                    }

                                    $person_list = array_unique($person_list);
                                    sort($person_list, SORT_NUMERIC);

                                    foreach ($person_list as $person):
                                        ?>
                                        <option value="<?php echo esc_attr($person); ?>">
                                            <?php echo esc_html($person); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-fieldset">
                                <label for="boat_cabin"><?php echo $icon['cabin']; ?> Cabin</label>
                                <select name="boat_cabin" id="boat_cabin" class="boat-listing-select2">
                                    <option value="">Select Cabin</option>
                                    <?php 

                                    // Extract and sort cabins - order -> 1,2,3,4
                                    $cabins_list = [];

                                    foreach ($getAvailableYachts as $cabins) {

                                        $c_data = json_decode($cabins['data'], true);

                                        // Safety check
                                        if (isset($c_data['cabins']) && $c_data['cabins'] > 0) {
                                            $cabins_list[] = (int) $c_data['cabins'];
                                        }
                                    }

                                    $cabins_list = array_unique($cabins_list);
                                    sort($cabins_list, SORT_NUMERIC);
                                    
                                    foreach( $cabins_list as $cabin): ?>

                                    <option value="<?php echo $cabin; ?>"><?php echo $cabin; ?></option>

                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="filter-fieldset">
                                <label for="boat_year"><?php echo $icon['date']; ?> Build Year</label>
                                <select name="boat_year" id="boat_year" class="boat-listing-select2">
                                    <option value="">Select Year</option>

                                    <?php
                                    $year_list = [];

                                    foreach ($boat_data as $boat) {

                                        // data যদি string হয় → decode
                                        if (is_string($boat['data'])) {
                                            $data = json_decode($boat['data'], true);
                                        } else {
                                            // data already array
                                            $data = $boat['data'];
                                        }

                                        if (!empty($data['year'])) {
                                            $year_list[] = (int) $data['year'];
                                        }
                                    }

                                    $year_list = array_unique($year_list);
                                    rsort($year_list, SORT_NUMERIC);

                                    foreach ($year_list as $year):
                                        ?>
                                        <option value="<?php echo esc_attr($year); ?>">
                                            <?php echo esc_html($year); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="contact-info">
                            <h5 class="title"><?php echo $icon['headphone']; ?>Help Center</h5>
                            <p>We're always just a message away</p>
                            <div class="contact-content">
                                <ul>
                                    <li><?php echo $icon['email_check']; ?> <a href="mailto:info@thebarefootparadise.com">Email Us</a></li>
                                    <li><?php echo $icon['phone']; ?> <a href="tel:1-284-342-2387">1-284-342-2387</a> or <a href="tel:340-201-8319">340-201-8319</a></li>
                                    <li><?php echo $icon['message']; ?> <a href="javascript:void(0)" id="boat-listing-open-hubspot-chat-box">Chat with us</a></li>
                                </ul>
                            </div>
                            <button  class="need-help-btn filter-page-contact-us-frm"> Contact Us <?php echo $icon['question_round']; ?></button>
                        </div>
                    </div>
                    <div class="boat-listing-area">
                        <div class="boat-count-and-pagination">
                            <div id="boat-count" class="boat-count-message">
                                <!-- boat count result show here -->
                            </div>

                            <div class="boat-listing-pagi">
                                <!-- get boat pagination from ajax -->
                            </div>
                        </div>

                        <div class="filter-summary-wrap" style="display: none;">
                            <div class="filtered-lists"></div>
                            <button id="reset-filters" class="reset-button">Reset Filter</button>
                        </div>
                        
                        <div class="boat-lists">
                            <!-- get boat list from ajax -->
                        </div>

                        <div class="boat-listing-pagi">
                            <!-- get boat pagination from ajax -->
                        </div>
                    </div>
                </div>
            </div>

        <?php

        $content = ob_get_clean();

        return $content;

    }
// End Filter ======================================================

    add_action('wp_ajax_bl_get_paginated_boats', 'bl_get_paginated_boats');
    add_action('wp_ajax_nopriv_bl_get_paginated_boats', 'bl_get_paginated_boats');

    function bl_get_paginated_boats() {

        // Increase timeout for slow price API
        set_time_limit(180); // 3 minutes
        ini_set('max_execution_time', 180);

        // Track timing for debugging
        $start_time = microtime(true);

        $helper = new Boat_Listing_Helper();
        

        $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
        $per_page = 10;

        $filters = [
            'charter_type'  => sanitize_text_field($_POST['charter_type'] ?? ''),
            'model'  => sanitize_text_field($_POST['model'] ?? ''),
            'company'  => sanitize_text_field($_POST['company'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'cabin'    => intval($_POST['cabin'] ?? 0),
            'person'    => intval($_POST['person'] ?? 0),
            'year'     => sanitize_text_field($_POST['year'] ?? ''),
            'free_yacht'     => sanitize_text_field($_POST['free_yacht'] ?? ''),
            'category'     => sanitize_text_field($_POST['category'] ?? ''),
        ];

        $datas = $helper->boat_filters($paged, $per_page, $filters);

//        echo "<pre>";
//        print_r($datas);
//        die;

        ob_start();

        if (!empty($datas['boats'])):

            foreach ($datas['boats'] as $raw):

                $data = $raw; // already decoded in boat_filters
                $main_image = $data['images'][0]['url'] ?? '';

                // Parse date range for URL parameters
                $date_from = '';
                $date_to = '';
                $has_date_filter = !empty($_POST['free_yacht'] ?? '');

                if ($has_date_filter) {
                    $date_input = $_POST['free_yacht'];
                    if (strpos($date_input, ' to ') !== false) {
                        list($start_date_str, $end_date_str) = explode(' to ', $date_input);
                        $date_from = date('Y-m-d', strtotime(trim($start_date_str)));
                        $date_to = date('Y-m-d', strtotime(trim($end_date_str)));
                    } else {
                        $date_from = date('Y-m-d', strtotime($date_input));
                        $date_to = date('Y-m-d', strtotime($date_input . ' +7 days'));
                    }
                }

                // Build details URL with date parameters if available
                $url_params = 'id=' . urlencode($data['id'] ?? '');
                if ($date_from && $date_to) {
                    $url_params .= '&dateFrom=' . urlencode($date_from) . '&dateTo=' . urlencode($date_to);
                }
                $single_url = esc_url(site_url('/boat-details/?' . $url_params));

                $icon = $helper->icons();

                ?>
                <div class="boat-list">
                    <div class="boat-img">
                        <a href="<?php echo esc_url($main_image); ?>" target="_blank">
                            <img src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($data['name'] ?? ''); ?>">
                        </a>
                    </div>
                    <div class="boat-info">
                        <div class="boat-title">
                            <a href="<?php echo esc_url($single_url); ?>" target="_blank">
                                <?php echo esc_html($data['name'] ?? ''); ?>
                            </a>
                            <?php if (!empty($data['homeBase'])): ?>
                                <span class="boat-location">
                                   <?php echo $icon['location']; ?>  <?php echo esc_html($data['homeBase']); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="other-title">
                            <div class="buildyear">
                                <?php echo $icon['model']; ?>
                                <?php echo 'Model: ' . esc_html($data['model'] ?? 'N/A'); ?>
                            </div>
                            <div class="buildyear">
                                <?php echo $icon['charter_type']; ?>
                                <?php echo 'Charter Type: ' . esc_html($data['kind'] ?? 'N/A'); ?>
                            </div>
                            <div class="buildyear">
                                <?php echo $icon['date']; ?>
                                <?php echo 'Build Year: ' . esc_html($data['year'] ?? 'N/A'); ?>
                            </div>
                            
                        </div>
                        <div class="features">
                            <ul>
                                <?php if (!empty($data['maxPeopleOnBoard']) && $data['maxPeopleOnBoard'] > 0): ?>
                                    <li>
                                        <?php echo $icon['person']; ?>
                                        <span>Persons</span>
                                        <span><?php echo esc_html($data['maxPeopleOnBoard']); ?></span>
                                    </li>
                                <?php endif; ?>

                                <li> <?php echo $icon['cabin']; ?> <span>Cabins</span> <span><?php echo esc_html($data['cabins'] ?? 'N/A'); ?></span></li>
                                <li> <?php echo $icon['shower']; ?> <span>Shower</span> <span><?php echo esc_html($data['wc'] ?? 'N/A'); ?></span></li>
                                <li> <?php echo $icon['salon']; ?> <span>Berths</span> <span><?php echo esc_html($data['berths'] ?? 'N/A'); ?></span></li>
                            </ul>
                        </div>
                        <?php if (!empty($raw['availability_year'])): ?>
                            <div class="availability-info">
                                <strong>Available Year:</strong> <?php echo esc_html($raw['availability_year']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="boat-btn">

                        <div class="price-info-wrap">
                            <?php
                            // Calculate number of days in date range (reuse parsed dates from above)
                            $days_count = 0;
                            if ($date_from && $date_to) {
                                $days_count = round((strtotime($date_to) - strtotime($date_from)) / (60 * 60 * 24));
                            }

                            // Check if price data is already available from price-first approach
                            if (!empty($data['price_info'])) {
                                $min = $data['price_info']['min'] ?? 0;
                                $max = $data['price_info']['max'] ?? 0;

                                echo '<div class="price-info">';
                                //echo $icon['euro'];
                                echo '<div style="display: flex; flex-direction: column; gap: 2px;">';

                                if ($min == $max) {
                                    echo '<span>' . number_format($min, 0) . ' ' . esc_html('€') . '</span>';
                                } else {
                                    echo '<span>' . number_format($min, 0) . ' - ' . number_format($max, 0) . ' ' . esc_html('€') . '</span>';
                                }

                                // Show "Price for X days"
                                if ($days_count > 0) {
                                    echo '<span style="font-size: 12px; color: #666;">Price for ' . $days_count . ' day' . ($days_count > 1 ? 's' : '') . '</span>';
                                }

                                echo '</div>';
                                echo '</div>';
                            } elseif ($has_date_filter) {
                                // Date filter applied but no price data (shouldn't happen with price-first)
                                echo '<div class="price-info">';
                                echo $icon['euro'];
                                echo '<span class="bl-spinner"></span>';
                                echo '</div>';
                            } else {
                                echo '<div class="price-info price-info--hint">';
                                echo '<p>📅 Select dates to see prices</p>';
                                echo '</div>';
                            }
                            ?>
                        </div>

                        <button class="boat-book-now"
                                data-id="<?php echo esc_attr($data['id']); ?>"
                                <?php if ($date_from): ?>data-date-from="<?php echo esc_attr($date_from); ?>"<?php endif; ?>
                                <?php if ($date_to): ?>data-date-to="<?php echo esc_attr($date_to); ?>"<?php endif; ?>
                        >
                            <?php _e('Book Now', 'boat-listing'); ?>
                            <span class="book-now-loader" style="display: none;"></span>
                        </button>
                        <a href="<?php echo $single_url; ?>">
                            <button class="boat-view"><?php _e('Details', 'boat-listing'); ?> <?php echo $icon['arrowright']; ?></button>
                        </a>
                    </div>
                </div>
            <?php endforeach;
        else:
            echo '<p>No Yacht found.</p>';
        endif;

    
        // This shortcode for booking modal
        echo do_shortcode('[book_now_modal_shortcode]');

        $boats_html = ob_get_clean();

        // Pagination HTML
        ob_start();

        if ($datas['pages'] > 1) {
            echo '<div class="pagination">';

            $current = $datas['paged'];
            $total_pages = $datas['pages'];
            $jump_interval = 20;
            $max_jumps_to_show = 2; // Show max 3 jump intervals on each side

            // Prev button
            if ($current > 1) {
                echo '<a href="#" class="bl-page" data-page="' . ($current - 1) . '">' . $icon["arrowleft"] . '</a>';
            }

            // Always show first page
            echo '<a href="#" class="bl-page ' . ($current == 1 ? 'active' : '') . '" data-page="1">1</a>';

            // Show pages 2-3 if we're on early pages
            if ($current <= 4) {
                if ($total_pages >= 2) {
                    echo '<a href="#" class="bl-page ' . ($current == 2 ? 'active' : '') . '" data-page="2">2</a>';
                }
                if ($total_pages >= 3) {
                    echo '<a href="#" class="bl-page ' . ($current == 3 ? 'active' : '') . '" data-page="3">3</a>';
                }
            }

            // Collect all jump intervals
            $jump_pages = [];
            for ($j = $jump_interval; $j < $total_pages; $j += $jump_interval) {
                $jump_pages[] = $j;
            }

            // Find jumps before and after current
            $jumps_before = [];
            $jumps_after = [];
            
            foreach ($jump_pages as $jump) {
                if ($jump < $current - 2) {
                    $jumps_before[] = $jump;
                } elseif ($jump > $current + 2) {
                    $jumps_after[] = $jump;
                }
            }

            // Show limited jump intervals BEFORE current (last N jumps before current)
            $jumps_before = array_slice($jumps_before, -$max_jumps_to_show);
            foreach ($jumps_before as $jump) {
                if ($jump > 3) { // Don't show if too close to page 1
                    echo '<span class="dots">..</span>';
                    echo '<a href="#" class="bl-page" data-page="' . $jump . '">' . $jump . '</a>';
                }
            }

            // Show ellipsis before current group if needed
            if ($current > 3 && (empty($jumps_before) || max($jumps_before) < $current - 2)) {
                echo '<span class="dots">..</span>';
            }

            // Pages around current page (current-1, current, current+1)
            if ($current > 3) {
                $start = max(2, $current - 1);
                $end = min($total_pages - 1, $current + 1);
                
                for ($i = $start; $i <= $end; $i++) {
                    echo '<a href="#" class="bl-page ' . ($i == $current ? 'active' : '') . '" data-page="' . $i . '">' . $i . '</a>';
                }
            }

            // Show limited jump intervals AFTER current (first N jumps after current)
            $jumps_after = array_slice($jumps_after, 0, $max_jumps_to_show);
            foreach ($jumps_after as $jump) {
                echo '<span class="dots">..</span>';
                echo '<a href="#" class="bl-page" data-page="' . $jump . '">' . $jump . '</a>';
            }

            // Ellipsis before last page
            if (!empty($jumps_after)) {
                $last_jump = end($jumps_after);
                if ($last_jump < $total_pages - 1) {
                    echo '<span class="dots">..</span>';
                }
            } elseif ($current < $total_pages - 3) {
                echo '<span class="dots">..</span>';
            }

            // Always show last page
            if ($total_pages > 1) {
                echo '<a href="#" class="bl-page ' . ($current == $total_pages ? 'active' : '') . '" data-page="' . $total_pages . '">' . $total_pages . '</a>';
            }

            // Next button
            if ($current < $total_pages) {
                echo '<a href="#" class="bl-page" data-page="' . ($current + 1) . '">' . $icon["arrowright"] . '</a>';
            }

            echo '</div>';
        }

         $pagination_html = ob_get_clean();

        // Calculate total execution time
        $total_time = microtime(true) - $start_time;

        // Clean any output that might have leaked
        if (ob_get_level()) {
            ob_clean();
        }

        wp_send_json_success([
            'boats_html' => $boats_html,
            'pagination_html' => $pagination_html,
            'total_boats' => $datas['total'],
            'has_date_filter' => !empty($filters['free_yacht']),
            'has_prices' => !empty($datas['has_prices']), // NEW: Indicate prices already fetched
            'date_range' => $filters['free_yacht'] ?? '',
            'timing' => [
                'total_time' => round($total_time, 2) . 's'
            ]
        ]);

        wp_die(); // Ensure clean exit
    }

    // New endpoint: Fetch prices asynchronously
    add_action('wp_ajax_bl_get_boat_prices', 'bl_get_boat_prices');
    add_action('wp_ajax_nopriv_bl_get_boat_prices', 'bl_get_boat_prices');

    function bl_get_boat_prices() {
        set_time_limit(300); // 5 minutes for slow API
        ini_set('max_execution_time', 300);

        $helper = new Boat_Listing_Helper();

        $date_range = sanitize_text_field($_POST['date_range'] ?? '');
        $boat_ids = isset($_POST['boat_ids']) ? array_map('sanitize_text_field', $_POST['boat_ids']) : [];

        if (empty($date_range) || empty($boat_ids)) {
            wp_send_json_error(['message' => 'Missing date range or boat IDs']);
        }

        try {
            // Parse date range
            if (strpos($date_range, ' to ') !== false) {
                list($start_date_str, $end_date_str) = explode(' to ', $date_range);
                $date_from = date('Y-m-d', strtotime(trim($start_date_str)));
                $date_to = date('Y-m-d', strtotime(trim($end_date_str)));
            } else {
                $date_from = date('Y-m-d', strtotime($date_range));
                $date_to = date('Y-m-d', strtotime($date_range . ' +7 days'));
            }

            error_log("Price API: Fetching prices for " . count($boat_ids) . " boats from $date_from to $date_to");

            $price_start = microtime(true);

            // Clear any previous output/errors
            if (ob_get_level()) {
                ob_clean();
            }

            $all_prices = $helper->get_all_yacht_prices_batch($date_from, $date_to);
            $price_time = microtime(true) - $price_start;

            error_log("Price API: Completed in " . round($price_time, 2) . "s");

            if (empty($all_prices) || !is_array($all_prices)) {
                error_log("⚠️ Price API returned empty or invalid data");
                wp_send_json_error(['message' => 'No prices available', 'fetch_time' => round($price_time, 2)]);
                return;
            }

            // Filter only requested boat IDs
            $result = [];
            foreach ($boat_ids as $boat_id) {
                if (isset($all_prices[$boat_id])) {
                    $result[$boat_id] = $all_prices[$boat_id];
                }
            }

            wp_send_json_success([
                'prices' => $result,
                'fetch_time' => round($price_time, 2),
                'boat_count' => count($result)
            ]);

        } catch (Exception $e) {
            error_log("Price API Error: " . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }

        wp_die();
    }
