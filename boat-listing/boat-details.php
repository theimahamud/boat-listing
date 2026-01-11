<?php

add_shortcode('boat_details', 'render_single_boat_by_query');

function render_single_boat_by_query() {
    $helper = new Boat_Listing_Helper();

    $current_year = date('Y');
    $boat_id = sanitize_text_field($_GET['yachtId'] ?? '');
    $country = sanitize_text_field($_GET['country'] ?? '');
    $productName = sanitize_text_field($_GET['productName'] ?? '');
    $charterType = sanitize_text_field($_GET['charterType'] ?? '');
    $cabin = sanitize_text_field($_GET['cabin'] ?? '');
    $year = sanitize_text_field($_GET['year'] ?? '');
    $person = sanitize_text_field($_GET['person'] ?? '');

    $default_date_from = $current_year . '-01-01T00:00:00';
    $default_date_to = $current_year . '-12-31T00:00:00';

    $date_from = sanitize_text_field($_GET['dateFrom'] ?? $default_date_from);
    $date_to   = sanitize_text_field($_GET['dateTo'] ?? $default_date_to);

    // Ensure dates have proper ISO time format if they don't already
    if (!empty($date_from) && strpos($date_from, 'T') === false) {
        $date_from .= 'T00:00:00';
    }
    if (!empty($date_to) && strpos($date_to, 'T') === false) {
        $date_to .= 'T00:00:00';
    }

    // Prepare all filters for API call including the specific yachtId
    $all_filters = [
            'country' => $country,
            'productName' => $productName,
            'charterType' => $charterType,
            'person' => $person,
            'cabin' => $cabin,
            'year' => $year,
    ];

    // Remove empty values but keep meaningful zeros and arrays
    $filters_for_api = array_filter($all_filters, function($value) {
        return $value !== '' && $value !== null && (!is_array($value) || !empty($value));
    });

    $boat_data = $helper->fetch_boat_data_cached($boat_id);
    $prices = $helper->get_single_yacht_offer_details_cached($boat_id, $date_from, $date_to, $filters_for_api);
    // ============================================================

    if (empty($boat_id)) {
        return '<p>No boat ID provided.</p>';
    }

    if (empty($boat_data)) {
        return '<p>Boat not found.</p>';
    }

    // Extract boat data from the result
    $data = $boat_data['data'] ?? [];

    if (empty($data)) {
        return '<p>Invalid boat data.</p>';
    }

    ob_start();
    ?>
    <div class="single-boat-details">

        <div class="single-boat-img">
            <div class="bl-slick-slider">
                <?php if (!empty($data['images'])): ?>
                    <?php foreach ($data['images'] as $img): ?>
                        <?php if (!empty($img['url'])): ?>
                            <div>
                                <a href="<?php echo esc_url($img['url']); ?>" data-fancybox="gallery">
                                    <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div>
                        <img src="<?php echo esc_url(plugins_url('assets/no-image.jpg', __FILE__)); ?>" alt="No image">
                    </div>
                <?php endif; ?>
            </div>
            <div class="bl-slick-slider-nav">
                <?php if (!empty($data['images'])): ?>
                    <?php foreach ($data['images'] as $img): ?>
                        <?php if (!empty($img['url'])): ?>
                            <div>
                                <img src="<?php echo esc_url($img['url']); ?>" alt="<?php echo esc_attr($data['name']); ?>">
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="boat-details">
            <h4>
                <?php
                echo esc_html($data['name']);

                if (!empty($data['homeBase'])) {
                    echo ' in <span class="location"><i class="ri-map-pin-line"></i> ' . esc_html($data['homeBase']) . '</span>';
                }
                ?>
            </h4>
            <div class="company-info info-card">
                <h5>Yacht Information</h5>
                <ul>
                    <li>
                        <span><?php _e('Price:', 'boat-listing'); ?></span>
                        <?php
                        if ($prices && $prices['min'] !== 'N/A' && $prices['max'] !== 'N/A') {
                            echo esc_html($prices['min'] . ' to ' . $prices['max'] . ' ' . $prices['currency']);
                        } else {
                            echo 'N/A';
                        }
                        ?>
                        <a href="#yachtPrice" class="open-tab-link">View Full List</a>
                    </li>
                    <li><span><?php _e('Yacht Type:', 'boat-listing'); ?></span> <?php echo !empty($data['kind']) ? esc_html($data['kind']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Cabins:', 'boat-listing'); ?></span> <?php echo !empty($data['cabins']) ? esc_html($data['cabins']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Yacht Age:', 'boat-listing'); ?></span> <?php echo !empty($data['year']) ? esc_html($data['year']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Beam (Feet):', 'boat-listing'); ?></span> <?php echo !empty($data['beam']) ? esc_html($data['beam']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Engine(s):', 'boat-listing'); ?></span> <?php echo !empty($data['engine']) ? esc_html($data['engine']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Engine(s) Power:', 'boat-listing'); ?></span> <?php echo !empty($data['engine']) ? esc_html($data['engine']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Fuel Capacity:', 'boat-listing'); ?></span> <?php echo !empty($data['fuelCapacity']) ? esc_html($data['fuelCapacity']) : __('N/A', 'boat-listing'); ?> </li>
                    <li><span><?php _e('Length (Feet):', 'boat-listing'); ?></span> <?php echo !empty($data['length']) ? esc_html($data['length']) : __('N/A', 'boat-listing'); ?> </li>
                </ul>
            </div>

            <button class="boat-book-now" data-id="<?php echo $boat_id; ?>">Request to book</button>
        </div>
    </div>

    <div class="bl-single-full-boat-details">
        <div class="details">

            <div class="title">Details</div>

            <div class="features">

                <div class="yachtdeails-info">
                    <div class="f-title">Features</div>
                    <ul>

                        <li><span><?php _e('Yacht Type:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['kind'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Model:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['model'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Yacht Age:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['year'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Max Persons:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['maxPeopleOnBoard'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Cabins:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['cabins'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Total Showers / WC:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['wc'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Berths (Total):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['berths'] ?? 'N/A'); ?>
                        </li>

                    </ul>
                </div>

                <div class="layout yachtdeails-info">
                    <div class="f-title">Layout</div>
                    <ul>
                        <li>
                            <span><?php _e('Yacht Layout:', 'boat-listing'); ?></span>
                            <?php
                            if (!empty($data['images'])) {
                                foreach ($data['images'] as $img) {
                                    if (!empty($img['url']) && stripos($img['name'], 'layout') !== false) {
                                        echo '<a href="' . esc_url($img['url']) . '" target="_blank">
                            <img src="' . esc_url($img['url']) . '" style="max-width:60px;margin:5px;">
                            </a>';
                                    }
                                }
                            }
                            ?>
                        </li>

                        <li><span><?php _e('Yacht Length (m):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['length'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Beam (m):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['beam'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Draft (m):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['draught'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Engine(s):', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['engine'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Fuel Capacity:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['fuelCapacity'] ?? 'N/A'); ?>
                        </li>

                        <li><span><?php _e('Water Capacity:', 'boat-listing'); ?></span>
                            <?php echo esc_html($data['waterCapacity'] ?? 'N/A'); ?>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <div class="location">
            <div class="title">Location</div>

            <div class="l-features">

                <ul>
                    <li>
                        <span><?php _e('Check In Time:', 'boat-listing'); ?></span>
                        <?php echo !empty($data['defaultCheckInTime']) ? esc_html($data['defaultCheckInTime']) : __('N/A', 'boat-listing'); ?>
                    </li>

                    <li>
                        <span><?php _e('Check Out Time:', 'boat-listing'); ?></span>
                        <?php echo !empty($data['defaultCheckOutTime']) ? esc_html($data['defaultCheckOutTime']) : __('N/A', 'boat-listing'); ?>
                    </li>
                    <li>
                        <span><?php _e('Location:', 'boat-listing'); ?></span>
                        <?php echo !empty($data['homeBase']) ? esc_html($data['homeBase']) : __('N/A', 'boat-listing'); ?>
                    </li>
                </ul>

                <div class="boat-map">
                    <?php
                    $homeBase = urlencode((string) ($data['homeBase'] ?? ''));
                    ?>

                    <iframe
                            src="https://maps.google.com/maps?q=<?php echo esc_attr($homeBase); ?>&z=14&output=embed"
                            width="100%"
                            height="200"
                            style="border:0;"
                            allowfullscreen
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
            </div>
        </div>

        <div class="price">
            <div class="title">Price Info</div>
            <div class="yachtPrice">
                <?php if (!empty($prices['rows'])): ?>
                    <table class="table table-stripe">
                        <tr>
                            <th>From</th>
                            <th>To</th>
                            <th>Start Location</th>
                            <th>End Location</th>
                            <th>Price</th>
                            <th>Currency</th>
                        </tr>

                        <?php foreach ($prices['rows'] as $row): ?>
                            <tr>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($row['dateFrom']))); ?></td>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($row['dateTo']))); ?></td>
                                <td><?php echo esc_html($row['startBase']); ?></td>
                                <td><?php echo esc_html($row['endBase']); ?></td>
                                <td><?php echo esc_html(number_format($row['startPrice'], 2)); ?></td>
                                <td><?php echo esc_html($row['currency']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p>Price not available for the selected dates. Please try different dates or contact us for pricing.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php
    // This shortcode for booking modal
    echo do_shortcode('[book_now_modal_shortcode]');
    ?>
    <?php

    return ob_get_clean();
}

/**
 * AJAX handler to clear details page cache
 */
//add_action('wp_ajax_bl_clear_details_cache', 'bl_clear_details_cache_handler');
//add_action('wp_ajax_nopriv_bl_clear_details_cache', 'bl_clear_details_cache_handler');

//function bl_clear_details_cache_handler() {
//    $boat_id = sanitize_text_field($_POST['boat_id'] ?? '');
//
//    if ($boat_id) {
//        delete_transient('bl_boat_' . $boat_id);
//
//        // Clear all price caches for this boat
//        global $wpdb;
//        $wpdb->query(
//                $wpdb->prepare(
//                        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
//                        '%_transient_bl_price_' . md5($boat_id . '%') . '%'
//                )
//        );
//
//        wp_send_json_success(['message' => 'Cache cleared for boat ' . $boat_id]);
//    } else {
//        wp_send_json_error(['message' => 'No boat ID provided']);
//    }
//}