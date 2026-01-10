<?php

$helper = new Boat_Listing_Helper();
$boat = $helper->fetch_all_boats();

$year = 2025;

$url = "https://www.booking-manager.com/api/v2/yachts?companyId=126";

$response = wp_remote_get($url, [
    'headers' => [
        'accept: application/json',
        'Authorization' => 'Bearer ' . get_option('bl_api_key')
    ]
]);

// Optional: Decode the JSON response
$datas = json_decode(wp_remote_retrieve_body($response), true);

?>


<div class="wrap">
    
    <form method="post">
        <?php wp_nonce_field('bl_api_setting_form_nonce'); ?>
        <input type="hidden" name="bl_api_setting_form" value="1">

        <h1>API Settings</h1>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="api_username">API Key</label></th>
                <td><input name="bl_api_key" type="text" id="bl_api_key" value="<?php echo esc_attr(get_option('bl_api_key', BL_API_KEY )); ?>" class="regular-text" required></td>
            </tr>
        </table><hr>

        <h1>Mail Settings</h1>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="bl_from_mail">From Mail</label></th>
                <td><input name="bl_from_mail" type="text" id="bl_from_mail" value="<?php echo esc_attr(get_option('bl_from_mail', get_option('admin_email'))); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="bl_from_name">From Name</label></th>
                <td><input name="bl_from_name" type="text" id="bl_from_name" value="<?php echo esc_attr(get_option('bl_from_name', get_bloginfo('name'))); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="bl_to_mail">To Mail</label></th>
                <td>
                    <input name="bl_to_mail" type="text" id="bl_to_mail" value="<?php echo esc_attr(get_option('bl_to_mail', get_option('admin_email') )); ?>" class="regular-text">
                    <p class="description">Add multiple email like: kawsarr575@gmail.com, mdrussel575@gmail.com </p>
                </td>
            </tr>
        </table>

        <?php submit_button('Save Settings'); ?>
    </form>

   
    <h1>Shortcode Settings</h1>
    <table class="form-table">
        <tr>
            <th scope="row"><label for="bl_boat_listing_shortcode">Boat Listing Shortcode</label></th>
            <td>Copy the shortcode: <strong>[boat_listing]</strong> and put in any page where you want to display the boat listing</td>
        </tr>
        <tr>
            <th scope="row"><label for="bl_single_boat_shortcode">Single Boat Shortcode</label></th>
            <td>Copy the shortcode: <strong>[boat_details]</strong> and page slug must be: <strong>boat-details</strong></td>
        </tr>
        <tr>
            <th scope="row"><label for="bl_single_boat_shortcode">Boat Filter Shortcode</label></th>
            <td>Copy the shortcode: <strong>[boat_filter]</strong> and page slug must be: <strong>boat-filter</strong></td>
        </tr>
        <tr>
            <th scope="row"><label for="bl_single_boat_shortcode">Boat Filter Home Page</label></th>
            <td>Copy the shortcode: <strong>[filter_desired_boat]</strong></td>
        </tr>
    </table>

    <div class="listing-sync">
        <h1>Sync Boats from NauSYS API</h1>
        <p>Click the button below to manually sync boats.</p>

        <div class="bl-boat-sync-buttons">

<!--            <div class="bl-boat-sync-button">-->
<!--                <button id="bl-sync-boat-company" class="button button-primary">Sync Boat Company</button>-->
<!--                <span id="bl-sync-spinner-company" style="display:none; vertical-align: middle;">-->
<!--                    <img src="--><?php //echo esc_url(admin_url('images/spinner.gif')); ?><!--" alt="Loading..." />-->
<!--                </span>-->
<!--            </div>-->

<!--            <div class="bl-boat-sync-button">-->
<!--                <button id="bl-sync-boats" class="button button-primary">Sync Boats</button>-->
<!--                <span id="bl-sync-spinner-boats" style="display:none; vertical-align: middle;">-->
<!--                    <img src="--><?php //echo esc_url(admin_url('images/spinner.gif')); ?><!--" alt="Loading..." />-->
<!--                </span>-->
<!--            </div>-->

<!--            <div class="bl-boat-sync-button">-->
<!--                <button id="bl-sync-models" class="button button-primary">Sync Models</button>-->
<!--                <span id="bl-sync-spinner-models" style="display:none; vertical-align: middle;">-->
<!--                    <img src="--><?php //echo esc_url(admin_url('images/spinner.gif')); ?><!--" alt="Loading..." />-->
<!--                </span>-->
<!--            </div>-->
  
<!--            <div class="bl-boat-sync-button">-->
<!--                <button id="bl-sync-price-list" class="button button-primary">Sync Price Lists</button>-->
<!--                <span id="bl-sync-spinner-price-list" style="display:none; vertical-align: middle;">-->
<!--                    <img src="--><?php //echo esc_url(admin_url('images/spinner.gif')); ?><!--" alt="Loading..." />-->
<!--                </span>-->
<!--            </div>-->
            
<!--            <div class="bl-boat-sync-button">-->
<!--                <button id="bl-sync-locations" class="button button-primary">Sync Location</button>-->
<!--                <span id="bl-sync-spinner-locations" style="display:none; vertical-align: middle;">-->
<!--                    <img src="--><?php //echo esc_url(admin_url('images/spinner.gif')); ?><!--" alt="Loading..." />-->
<!--                </span>-->
<!--            </div>-->

            <div class="bl-boat-sync-button">
                <button id="bl-sync-category" class="button button-primary">Sync Boat Type</button>
                <span id="bl-sync-spinner-category" style="display:none; vertical-align: middle;">
                    <img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" alt="Loading..." />
                </span>
            </div>

            <div class="bl-boat-sync-button">
                <button id="bl-sync-regions" class="button button-primary">Sync World Regions</button>
                <span id="bl-sync-spinner-regions" style="display:none; vertical-align: middle;">
                    <img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" alt="Loading..." />
                </span>
            </div>

            <div class="bl-boat-sync-button">
                <button id="bl-sync-country" class="button button-primary">Sync Countries</button>
                <span id="bl-sync-spinner-country" style="display:none; vertical-align: middle;">
                    <img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" alt="Loading..." />
                </span>
            </div>

            <div class="bl-boat-sync-button">
                <button id="bl-sync-charterbase" class="button button-primary">Sync Base</button>
                <span id="bl-sync-spinner-charterbase" style="display:none; vertical-align: middle;">
                    <img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" alt="Loading..." />
                </span>
            </div>


<!--            <div class="bl-boat-sync-button">-->
<!--                <button id="bl-sync-countrystate" class="button button-primary">Sync Country States</button>-->
<!--                <span id="bl-sync-spinner-countrystate" style="display:none; vertical-align: middle;">-->
<!--                    <img src="--><?php //echo esc_url(admin_url('images/spinner.gif')); ?><!--" alt="Loading..." />-->
<!--                </span>-->
<!--            </div>-->

<!--            <div class="bl-boat-sync-button">-->
<!--                <button id="bl-sync-freeyacht" class="button button-primary">Sync Free Yacht</button>-->
<!--                <span id="bl-sync-spinner-freeyacht" style="display:none; vertical-align: middle;">-->
<!--                    <img src="--><?php //echo esc_url(admin_url('images/spinner.gif')); ?><!--" alt="Loading..." />-->
<!--                </span>-->
<!--            </div>-->

        </div>

        <!-- <p id="bl-sync-status" style="margin-top: 15px;"></p> -->

        <div style="margin-top:15px;">
            <div id="bl-sync-status" style="
                max-height:400px;
                overflow:auto;
                padding:10px;
                font-family: monospace;
                font-size:13px;
                line-height:1.5;
                white-space: pre-wrap;
            ">
                <!-- initially empty -->
            </div>
        </div>
        
    </div>

</div>
