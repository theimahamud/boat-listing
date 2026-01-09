<?php


add_action('wp_ajax_bl_sync_boat_company', 'bl_sync_boat_company');

function bl_sync_boat_company() {

    //check_ajax_referer('bl_sync_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];

    // Sync Companies
    $companies_synced = Boat_Listing_Activator::insert_companies();
    $results['companies'] = [
        'status' => $companies_synced ? 'success' : 'failed',
        'count'  => $companies_synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_companies") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Sync Successful.',
        'results' => $results,
    ]);

}


add_action('wp_ajax_bl_sync_freeyacht', 'bl_sync_freeyacht');

function bl_sync_freeyacht() {

    //check_ajax_referer('bl_sync_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];

    // Sync Companies
    $companies_synced = Boat_Listing_Activator::insert_freeyacht();
    $results['freeyacht'] = [
        'status' => $companies_synced ? 'success' : 'failed',
        'count'  => $companies_synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_free") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Sync Successful.',
        'results' => $results,
    ]);

}

/**
 * Start Boat Sync Process
 */

add_action('wp_ajax_bl_start_boat_sync', function () {

    if (! current_user_can('manage_options')) {
        wp_send_json_error();
    }

    // 🔒 already running? do nothing
    if (get_option('bl_boat_sync_active')) {
        wp_send_json_success([
                'message' => 'Sync already running'
        ]);
    }

    // ✅ just activate sync
    update_option('bl_boat_sync_active', true);

    if (! wp_next_scheduled('bl_boat_sync_cron')) {
        wp_schedule_event(time(), 'every_minute', 'bl_boat_sync_cron');
    }

    wp_send_json_success([
            'message' => 'Boat sync started. Background cron is running.'
    ]);
});


/**
 * Sync Boat Models
 */

add_action('wp_ajax_bl_sync_boat_models', 'bl_sync_boat_models');

function bl_sync_boat_models() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];


    //Sync models
    $models_synced = Boat_Listing_Activator::insert_models();
    $results['models'] = [
        'status' => $models_synced ? 'success' : 'failed',
        'count'  => $models_synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_models") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Models Sync Successful.',
        'results' => $results,
    ]);

}

/**
 * Sync Boat Price Lists
 */

add_action('wp_ajax_bl_sync_boat_price_lists', 'bl_sync_boat_price_lists');

function bl_sync_boat_price_lists() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];


    //Sync models
    $synced = Boat_Listing_Activator::insert_price_list();
    $results['price_lists'] = [
        'status' => $synced ? 'success' : 'failed',
        'count'  => $synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_price_list") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Price Lists Sync Successful.',
        'results' => $results,
    ]);

}

/**
 * Sync Boat Category
 */
add_action('wp_ajax_bl_sync_boat_category', 'bl_sync_boat_category');

function bl_sync_boat_category() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];

     // Sync locations
    $synced = Boat_Listing_Activator::insert_category();
    $results['categories'] = [
        'status' => $synced ? 'success' : 'failed',
        'count'  => $synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_category") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Categories Sync Successful.',
        'results' => $results,
    ]);
}


/**
 * Sync Boat country
 */

add_action('wp_ajax_bl_sync_boat_country', 'bl_sync_boat_country');

function bl_sync_boat_country() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];


     // Sync country
    $synced = Boat_Listing_Activator::insert_country();
    $results['country'] = [
        'status' => $synced ? 'success' : 'failed',
        'count'  => $synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_country") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Countries Sync Successful.',
        'results' => $results,
    ]);

}


/**
 * Sync Boat country state
 */

add_action('wp_ajax_bl_sync_boat_country_state', 'bl_sync_boat_country_state');

function bl_sync_boat_country_state() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];


     // Sync country state
    $synced = Boat_Listing_Activator::insert_country_state();
    $results['country_state'] = [
        'status' => $synced ? 'success' : 'failed',
        'count'  => $synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_country_state") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Country states Sync Successful.',
        'results' => $results,
    ]);

}

/**
 * Sync Boat regions
 */

add_action('wp_ajax_bl_sync_boat_regions', 'bl_sync_boat_regions');

function bl_sync_boat_regions() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];


     // Sync regions
    $synced = Boat_Listing_Activator::insert_regions();
    $results['regions'] = [
        'status' => $synced ? 'success' : 'failed',
        'count'  => $synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_regions") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Regions Sync Successful.',
        'results' => $results,
    ]);

}

/**
 * Sync Boat Locations
 */

add_action('wp_ajax_bl_sync_boat_locations', 'bl_sync_boat_locations');

function bl_sync_boat_locations() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];


     // Sync locations
    $location_synced = Boat_Listing_Activator::insert_locaitons();
    $results['locations'] = [
        'status' => $location_synced ? 'success' : 'failed',
        'count'  => $location_synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_locations") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'Locations Sync Successful.',
        'results' => $results,
    ]);

}

/**
 * Sync Boat boat_charterbaese
 */

add_action('wp_ajax_bl_sync_boat_charterbase', 'bl_sync_boat_charterbase');

function bl_sync_boat_charterbase() {


    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Unauthorized']);
    }

    // Call the fetch & insert function
    require_once plugin_dir_path(__FILE__) . 'class-boat-listing-activator.php'; // adjust path as needed

    global $wpdb;

    $results = [];


    // Sync Charter Bases
    $charterbase_synced = Boat_Listing_Activator::insert_charterbases();
    $results['charterbases'] = [
        'status' => $charterbase_synced ? 'success' : 'failed',
        'count'  => $charterbase_synced ? (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}boat_charterbaese") : 0,
    ];

      // Final response
    wp_send_json_success([
        'message' => 'charterbase Sync Successful.',
        'results' => $results,
    ]);

}

// Show booking form on modal
add_action('wp_ajax_bl_load_booking_modal', 'bl_load_booking_modal');
add_action('wp_ajax_nopriv_bl_load_booking_modal', 'bl_load_booking_modal');

function bl_load_booking_modal() {
    
    $helper =  new Boat_Listing_Helper();
   
    $boat_id = intval($_POST['boat_id'] ?? 0);

    $get_icons = $helper->icons();

    $get_boat_data = $helper->fetch_all_boats( $boat_id );
    $boat_name = $get_boat_data['data']['name'];

    $get_boat_location = $helper->fetch_all_locations( $get_boat_data['data']['locationId'] );
    $boat_location = $get_boat_location['location_data']['name']['textEN'];

    //print_r($boat_location);


    if (!$boat_id) {
        echo '<p>Invalid boat ID.</p>';
        wp_die();
    }

    ?>

    <div class="modal-header boat-listing-modal-header">
        <h4 class="modal-title">Book  <a class="boat-title" target="_blank" href="<?php echo site_url('/boat-details/?id=' . $boat_id); ?>"><?php echo esc_html($boat_name); ?></a> In <span class="boat-location"><?php echo $get_icons['location'] . esc_html($boat_location); ?></span></h4>
        <span class="btn-close" data-bs-dismiss="modal" aria-label="Close"></span>
    </div>

    <div class="modal-body boat-listing-modal-body">
        <form id="boat-booking-form" class="boat-booking-form" method="post" novalidate>

            <input type="hidden" name="boat_id" value="<?php echo esc_html($boat_id) ?>">

            <div class="form-row row">
                <div class="form-group col-12">
                        <label for="book_date">Select booking date <span class="text-danger">*</span></label>
                      <input type="text" id="book_date" name="book_date" required placeholder="Select date range" autocomplete="off" class="boat-listing-input-text bl-date-range-picker" />
                      <div class="invalid-feedback">Please select booking date</div>
                </div>
            </div>
            <!-- Row 1: Full Name & Email -->
            <div class="form-row row">
                <div class="form-group col-4">
                    <label for="full_name">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" placeholder="John Doe" class="boat-listing-input-text" id="full_name" required />
                    <div class="invalid-feedback">Please enter your full name</div>
                </div>
                <div class="form-group col-4">
                    <label for="email">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" placeholder="example@gmail.com" class="boat-listing-input-text" id="email" required />
                    <div class="invalid-feedback">Please enter your valid email address</div>
                </div>
                <div class="form-group col-4">
                    <label for="contact">Contact Number <span class="text-danger">*</span></label>
                    <input type="tel" name="contact" placeholder="+1 (310) 555-1234" class="boat-listing-input-text boat-listing-format-contact" id="contact" required />
                    <div class="invalid-feedback">Please enter your contact number</div>
                </div>
            </div>

            <!-- Row 2: Contact & Location -->
            <div class="form-row row">
                
                <div class="form-group col-12">
                    <label for="address">Full Address <span class="text-danger">*</span></label>
                    <textarea name="address" rows="3" placeholder="1234 Elm Street,  Apt. 5B,  Los Angeles, CA 90001" id="address" class="boat-listing-input-textarea" required></textarea>
                    <div class="invalid-feedback">Please enter your address</div>
                    <!-- //<input type="text" name="location" placeholder="Dubrovnik" class="boat-listing-input-text" id="location" required /> -->
                </div>
            </div>

            <!-- Additional Services -->
            <div class="form-row row">
                <div class="form-group col-12">
                    <label>Additional Services <span class="text-danger">*</span></label>
                    <select name="additional_services[]" class="boat-listing-select2 boat-listing-select" required multiple="multiple">
                        <option value="Water Taxi">Water Taxi</option>
                        <option value="Center-Console Rental">Center-Console Rental</option>
                        <option value="Sub Rentals">SUV rentals</option>
                        <option value="Souvenirs">Souvenirs</option>
                        <option value="Souvenirs">Concierge</option>
                        <option value="Land Taxi (STT)">Land Taxi (STT)</option>
                        <option value="Land Taxi (BVI)">Land Taxi (BVI)</option>
                        <option value="Travel Protection">Travel Protection</option>
                        <option value="None">None</option>
                    </select>
                    <div class="invalid-feedback">Please select minimum one service but If you don't need select "None" Option</div>
                </div>
            </div>
            
            <div class="form-row row">
                <div class="form-group col-12">
                    <button type="submit" class="book-now-on-popup">Book Now</button>
                    <div class="message"></div>
                </div>
             </div>

        </form>
    </div>

    <?php

    

    wp_die();
}

// Insert Book reserve
add_action('wp_ajax_bl_insert_book_reserve', 'bl_insert_book_reserve');
add_action('wp_ajax_nopriv_bl_insert_book_reserve', 'bl_insert_book_reserve');

function bl_insert_book_reserve() {
    global $wpdb;

    $table = $wpdb->prefix . 'boat_book_request'; // your table name

    $boat_id = isset($_POST['boat_id']) ? intval($_POST['boat_id']) : 0;

    parse_str($_POST['form_data'], $parsed_data);

    $sanitized_data = [];
    foreach ( $parsed_data as $key => $value ) {
        if ( is_array( $value ) ) {
            // For arrays like additional_services[]
            $sanitized_data[$key] = array_map( 'sanitize_text_field', $value );
        } else {
            $sanitized_data[$key] = sanitize_text_field( $value );
        }
    }
    
    $json_date = wp_json_encode($sanitized_data);

    if (!$boat_id || empty($json_date)) {
        wp_send_json_error('Missing data');
    }

    $data = array(
        'boat_id'      => $boat_id,
        'book_data'    => $json_date,
        'mail_status'    => 'pending',
        'inserted_at'  => current_time('mysql')
    );

    $format = array('%d', '%s', '%s', '%s');

    $inserted = $wpdb->insert($table, $data, $format);

    if ($inserted) {
        wp_send_json_success();
    } else {
        wp_send_json_error('DB insert failed');
    }

    wp_die();
}