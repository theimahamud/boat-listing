<?php
add_shortcode('boat_filter', 'render_boat_listing_filter');

function render_boat_listing_filter($atts, $content = null){

    $helper = new Boat_Listing_Helper();
    $icon = $helper->icons();
    $charterTypes = $helper->fetch_category();
    $preloader = $helper->bl_render_boat_overlay_preloader();
    $getBoatProductTypes = $helper->getBoatProductTypes();
    $data = $helper->sortPriorityCountries();
    $getYears = $helper->getBoatYears();
    $getCabins = $helper->getBoatCabins();
    $getBoatPersons = $helper->getBoatPersons();

    $regionsOrdered     = $data['regions'];
    $remainingCountries = $data['countries'];

    ob_start();

    ?>
    <div class="boat-listing-filter-area" style="position:relative">
        <div class="boat-lists-loader"><?php echo $preloader; ?></div>

        <div class="boat-listing-filter-wraper">
            <div class="filter-bar-area">
                <div class="filter-bar">
                    <strong><?php echo $icon['filter']; ?> Filter By</strong>
                    <div class="filter-fieldset">
                        <label for="charterType"><?php echo $icon['charter_type']; ?> Charter Type</label>
                        <select name="charterType" id="charterType" class="boat-listing-select2">
                            <option value="">Select Charter Type </option>
                            <?php foreach ($charterTypes as $category): ?>
                                <option value="<?php echo esc_attr($category['name']); ?>">
                                    <?php echo esc_html($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date filter -->
                    <!-- Hidden API fields -->
                    <input type="hidden" name="dateFrom" id="dateFrom">
                    <input type="hidden" name="dateTo" id="dateTo">
                    <div class="filter-fieldset">
                        <label for="dateRange"><?php echo $icon['date']; ?> Dates </label>
                        <input type="text" id="dateRange" name="dateRange" placeholder="Select date range" autocomplete="off" class="boat-listing-input-text bl-date-range-picker" />
                    </div>

                    <!-- Regions -->
                    <div class="filter-fieldset">
                        <label for="country"><?php echo $icon['location']; ?> Destinations</label>
                        <select name="country" id="country" class="boat-listing-select2">
                            <option value="">All Destinations</option>
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
                                            <option value="<?php echo esc_attr($country['country_data']['shortName']); ?>">
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
                        <label for="productName"><?php echo $icon['category']; ?> Category</label>
                        <select name="productName" id="productName" class="boat-listing-select2">
                            <option value="">Select Category</option>
                            <?php
                            foreach ($getBoatProductTypes as $productType):
                                ?>
                                <option value="<?php echo esc_attr($productType); ?>">
                                    <?php echo esc_html($productType); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!--Person filter -->
                    <div class="filter-fieldset">
                        <label for="people"><?php echo $icon['person']; ?> Person</label>
                        <select name="people" id="people" class="boat-listing-select2">
                            <option value="">Select Person</option>
                            <?php foreach ($getBoatPersons as $person):
                                ?>
                                <option value="<?php echo esc_attr($person); ?>">
                                    <?php echo esc_html($person); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-fieldset">
                        <label for="cabin"><?php echo $icon['cabin']; ?> Cabin</label>
                        <select name="cabin" id="cabin" class="boat-listing-select2">
                            <option value="">Select Cabin</option>
                            <?php foreach( $getCabins as $cabin): ?>

                                <option value="<?php echo $cabin; ?>"><?php echo $cabin; ?></option>

                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-fieldset">
                        <label for="year"><?php echo $icon['date']; ?> Build Year</label>
                        <select name="year" id="year" class="boat-listing-select2">
                            <option value="">Select Year</option>
                            <?php foreach ($getYears as $year):
                                ?>
                                <option value="<?php echo esc_attr($year); ?>">
                                    <?php echo esc_html($year); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button id="submitFilter" type="button">Filter</button>
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

    $helper = new Boat_Listing_Helper();

    // Check if this is a local-filters-only request
    $local_filters_only = isset($_POST['local_filters_only']) && $_POST['local_filters_only'] === 'true';

    $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
    $per_page = 10;

    $filters = [
            'charter_type'  => sanitize_text_field($_POST['charter_type'] ?? $_GET['charterType'] ?? ''),
            'model'  => sanitize_text_field($_POST['model'] ?? ''),
            'company'  => sanitize_text_field($_POST['company'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'cabin'    => intval($_POST['cabin'] ?? $_GET['cabin'] ?? 0),
            'person'    => intval($_POST['person'] ?? $_GET['person'] ?? 0),
            'year'     => sanitize_text_field($_POST['year'] ?? $_GET['year'] ?? ''),
            'free_yacht'     => sanitize_text_field($_POST['free_yacht'] ?? ''),
            'category'     => sanitize_text_field($_POST['category'] ?? ''),
    ];

    // Debug filters
    error_log("🔍 AJAX Filters received (total: " . count(array_filter($filters)) . "):");
    foreach($filters as $key => $value) {
        if (!empty($value)) {
            error_log("  - {$key}: {$value}");
        }
    }

    $country = isset($_GET['country']) && $_GET['country'] ? $_GET['country'] : '';
    $productName = isset($_GET['productName']) && $_GET['productName'] ? sanitize_text_field($_GET['productName']) : '';
    $dateFrom = isset($_GET['dateFrom']) && $_GET['dateFrom'] ? $_GET['dateFrom'] : '';
    $dateTo = isset($_GET['dateTo']) && $_GET['dateTo'] ? $_GET['dateTo'] : '';

    // Debug URL parameters
    error_log("🔍 URL Parameters:");
    error_log("  - country: " . $country);
    error_log("  - productName: " . $productName);
    if (!empty($_GET['charterType'])) error_log("  - charterType: " . $_GET['charterType']);
    if (!empty($_GET['person'])) error_log("  - person: " . $_GET['person']);
    if (!empty($_GET['cabin'])) error_log("  - cabin: " . $_GET['cabin']);
    if (!empty($_GET['year'])) error_log("  - year: " . $_GET['year']);

    $icon = $helper->icons();

  // Handle local-filters-only requests (only when NO dates are provided)
    if ($local_filters_only && (empty($dateFrom) || empty($dateTo))) {
        error_log("🏠 Local filters only request (no dates) - using direct local search");
        $datas = $helper->search_local_boats($filters, $per_page, $paged);
        if (!empty($datas['boats'])) {
            error_log("✅ Local-only AJAX search found " . count($datas['boats']) . " boats");
        } else {
            error_log("❌ Local-only AJAX search returned empty results");
        }
    } else {
        // Normal API + local filtering (when dates are provided or no local_filters_only flag)
        if ($local_filters_only && (!empty($dateFrom) && !empty($dateTo))) {
            error_log("📡 Local filters only requested BUT dates provided - using API + local filtering instead");
        }
        $datas = $helper->boat_filters($country, $productName, $dateFrom, $dateTo, $paged, $per_page, $filters);
    }

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

            // Build details URL - CORRECTED VERSION
            $url_params = 'yachtId=' . urlencode($data['id'] ?? '');

            // Add date parameters if available (ensure ISO time format)
            if ($date_from && $date_to) {
                $formatted_date_from = $date_from;
                $formatted_date_to = $date_to;

                // Add time component if missing
                if (!empty($formatted_date_from) && strpos($formatted_date_from, 'T') === false) {
                    $formatted_date_from .= 'T00:00:00';
                }
                if (!empty($formatted_date_to) && strpos($formatted_date_to, 'T') === false) {
                    $formatted_date_to .= 'T00:00:00';
                }

                $url_params .= '&dateFrom=' . urlencode($formatted_date_from) . '&dateTo=' . urlencode($formatted_date_to);
            }

            // Include ALL current filter parameters from URL so user can return to exact filter state
            $current_filters = [
                    'person' => $_GET['person'] ?? '',
                    'cabin' => $_GET['cabin'] ?? '',
                    'year' => $_GET['year'] ?? '',
                    'country' => $country,
                    'productName' => $productName,
                    'charterType' => $_GET['charterType'] ?? '',
            ];

            foreach ($current_filters as $param => $value) {
                if ($value !== '') {
                    $url_params .= '&' . $param . '=' . urlencode($value);
                }
            }

            $single_url = esc_url(site_url('/boat-details/?' . $url_params));
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
                </div>

                <div class="boat-btn">

                    <div class="price-info-wrap">
                        <?php
                        // Show offer price if available from API
                        if (!empty($data['offer']['price'])) {
                            $price = number_format($data['offer']['startPrice'], 0);
//                                $currency = $data['offer']['currency'] ?? 'EUR';
                            $currency = '€';

                            echo '<div class="price-info">';
                            echo '<p><i class="ri-money-euro-circle-line" style="margin-right:4px;"></i>';
                            echo 'Price: ' . $price . ' ' . esc_html($currency) . '</p>';
                            echo '</div>';
                        } else {
                            echo '<div class="price-info">';
                            echo '<p><i class="ri-money-euro-circle-line" style="margin-right:4px;"></i>';
                            echo 'Price: N/A</p>';
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
        $message = isset($datas['message']) ? $datas['message'] : 'No boats found for your search criteria.';
        echo '<div class="no-boats-found" style="text-align:center; padding:40px;">';
        echo '<h3>🚫 ' . $message . '</h3>';
    endif;

    // This shortcode for booking modal
    echo do_shortcode('[book_now_modal_shortcode]');

    $boats_html = ob_get_clean();

    // Pagination HTML
    ob_start();

    error_log("🔍 Pagination Debug:");
    error_log("  - Total pages: " . $datas['pages']);
    error_log("  - Current page: " . $datas['paged']);
    error_log("  - Total boats: " . $datas['total']);

    if ($datas['pages'] > 1) {
        $current = $datas['paged'];
        $total_pages = $datas['pages'];

        error_log("  ✅ Generating pagination HTML");
        error_log("  - Current page: " . $current);
        error_log("  - Total pages: " . $total_pages);

        echo '<div class="pagination">';


        // Previous button
        if ($current > 1) {
            echo '<a href="#" class="bl-page" data-page="' . ($current - 1) . '">' . $icon["arrowleft"] . '</a>';
        }

        // Simple pagination logic
        $start_page = max(1, $current - 2);
        $end_page = min($total_pages, $current + 2);

        error_log("  - Start page: " . $start_page);
        error_log("  - End page: " . $end_page);

        // Show dots before if needed
        if ($start_page > 1) {
            echo '<a href="#" class="bl-page" data-page="1">1</a>';
            if ($start_page > 2) {
                echo '<span class="dots">...</span>';
            }
        }

        // Show page numbers
        for ($i = $start_page; $i <= $end_page; $i++) {
            $active_class = ($i == $current) ? ' active' : '';
            echo '<a href="#" class="bl-page' . $active_class . '" data-page="' . $i . '">' . $i . '</a>';
            error_log("  - Generated page: " . $i . ($i == $current ? ' (active)' : ''));
        }

        // Show dots after if needed
        if ($end_page < $total_pages) {
            if ($end_page < $total_pages - 1) {
                echo '<span class="dots">...</span>';
            }
            echo '<a href="#" class="bl-page" data-page="' . $total_pages . '">' . $total_pages . '</a>';
            error_log("  - Generated last page: " . $total_pages);
        }

        // Next button
        if ($current < $total_pages) {
            echo '<a href="#" class="bl-page" data-page="' . ($current + 1) . '">' . $icon["arrowright"] . '</a>';
        }

        echo '</div>';
    } else {
        error_log("  ❌ No pagination generated - pages: " . $datas['pages']);
    }

    $pagination_html = ob_get_clean();

    // Clean any output that might have leaked
    if (ob_get_level()) {
        ob_clean();
    }

    wp_send_json_success([
            'boats_html' => $boats_html,
            'pagination_html' => $pagination_html,
            'total_boats' => $datas['total'],
            'has_date_filter' => !empty($filters['free_yacht']),
            'has_prices' => !empty($datas['has_prices']),
            'date_range' => $filters['free_yacht'] ?? ''
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
