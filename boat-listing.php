<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://designdevzone.com
 * @since             1.0.0
 * @package           Boat_Listing
 *
 * @wordpress-plugin
 * Plugin Name:       Boat Listing
 * Plugin URI:        https://designdevzone.com
 * Description:       This is a description of the plugin.
 * Version:           1.0.0
 * Author:            Design Develop Zone
 * Author URI:        https://designdevzone.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       boat-listing
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BOAT_LISTING_VERSION', '1.0.0' );
define( 'BL_TEXT_DOMAIN', 'boat-listing' );
define( 'BL_API_KEY', '1ee7-6ec10329a42145148a4a447d18cd7ea1245d7d992df2f5c303190d0b25cfa718c5d4c1b3a41288c9583201ce87cd967f16db2aac4828eebc7d2159406652d9a9' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-boat-listing-activator.php
 */
require_once plugin_dir_path(__FILE__) . 'includes/class-boat-listing-activator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-boat-listing-deactivator.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-boat-listing.php';

/**
 * --------------------------------------------------
 * Custom cron interval
 * --------------------------------------------------
 */
add_filter('cron_schedules', function ($schedules) {

    // existing
    if (!isset($schedules['every_minute'])) {
        $schedules['every_minute'] = [
            'interval' => 60,
            'display'  => 'Every Minute',
        ];
    }

    return $schedules;
});
/**
 * --------------------------------------------------
 * Plugin activation
 * --------------------------------------------------
 */
register_activation_hook(__FILE__, function () {

    // DB + setup
    Boat_Listing_Activator::activate();

    if (!wp_next_scheduled('bl_daily_availability_sync')) {
        wp_schedule_event(time(), 'hourly', 'bl_daily_availability_sync');
    }

    // Schedule cron safely
    if (!wp_next_scheduled('bl_boat_sync_cron')) {
        wp_schedule_event(time(), 'hourly', 'bl_boat_sync_cron');
    }
});

/**
 * --------------------------------------------------
 * Plugin deactivation
 * --------------------------------------------------
 */
register_deactivation_hook(__FILE__, function () {
    wp_clear_scheduled_hook('bl_boat_sync_cron');
    wp_clear_scheduled_hook('bl_daily_availability_sync');
    Boat_Listing_Deactivator::deactivate();
});

/**
 * --------------------------------------------------
 * CRON ACTION (must be global & early)
 * --------------------------------------------------
 */
add_action(
    'bl_daily_availability_sync',
    ['Boat_Listing_Activator', 'sync_yacht_availability']
);

add_action(
    'bl_boat_sync_cron',
    ['Boat_Listing_Activator', 'process_boat_sync']
);

/**
 * --------------------------------------------------
 * Run plugin
 * --------------------------------------------------
 */
function run_boat_listing() {
    $plugin = new Boat_Listing();
    $plugin->run();
}
run_boat_listing();
