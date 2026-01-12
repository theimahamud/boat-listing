<?php


/**
 * Fired during plugin activation
 *
 * @link       https://designdevzone.com
 * @since      1.0.0
 *
 * @package    Boat_Listing
 * @subpackage Boat_Listing/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Boat_Listing
 * @subpackage Boat_Listing/includes
 * @author     Design Develop Zone <kawsarr575@gmail.com>
 */
class Boat_Listing_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		self::create_table();
	}

	public static function cread(){

        return get_option('bl_api_key');
    }

	public static function create_table (){

		global $wpdb;

		$boat_table = $wpdb->prefix . 'boats';
		$company_table = $wpdb->prefix . 'boat_companies';
		$boat_category = $wpdb->prefix . 'boat_category';
		$charterbase_table = $wpdb->prefix . 'boat_charterbaese';
		$book_reserve_table = $wpdb->prefix . 'boat_book_request';
        $boat_country_table = $wpdb->prefix . 'boat_country';
        $boat_regions_table = $wpdb->prefix . 'boat_regions';
        $yacht_availability_table = $wpdb->prefix . 'yacht_availability';
		$charset_collate = $wpdb->get_charset_collate();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$boat = "CREATE TABLE $boat_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
			data LONGTEXT NOT NULL
		) $charset_collate;";
        dbDelta($boat);

		$boat_country = "CREATE TABLE $boat_country_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
            country_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($boat_country);

		$boat_regions = "CREATE TABLE $boat_regions_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
            regions_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($boat_regions);

        $sql = "CREATE TABLE $yacht_availability_table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            yacht_id BIGINT(20) UNSIGNED NOT NULL,
            year SMALLINT(4) NOT NULL,
            availability_string LONGTEXT NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        
            PRIMARY KEY (id),
            UNIQUE KEY yacht_year (yacht_id, year),
            KEY idx_yacht (yacht_id),
            KEY idx_year (year)
        ) $charset_collate;";
        dbDelta($sql);

        $company = "CREATE TABLE $company_table (
            id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
            company_data LONGTEXT NOT NULL
        ) $charset_collate;";
        dbDelta($company);

        $category = "CREATE TABLE $boat_category (
			id BIGINT AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(255),
			category_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($category);

		$location = "CREATE TABLE $location_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
			location_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($location);

		$charterbases = "CREATE TABLE $charterbase_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
			base_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($charterbases);

		$book_reserve = "CREATE TABLE $book_reserve_table (
			id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			boat_id BIGINT(20) NOT NULL,
			book_data LONGTEXT NOT NULL,
			mail_status TEXT NOT NULL,
			inserted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
		) $charset_collate;";

		dbDelta($book_reserve);

	}

    public static function insert_country() {

		global $wpdb;
        $table_name = $wpdb->prefix . 'boat_country';

        $url = 'https://www.booking-manager.com/api/v2/countries';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer " . self::cread(),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $datas = json_decode($response, true);

        //print_r($datas); die;

		if (empty($datas)) {
            error_log('Booking Manager API returned empty company data.');
            return false;
        }

		// Insert into database
        foreach ($datas as $data) {
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $wpdb->replace($table_name, [
                'id' => $id,
                'country_data' => $json_data,
            ]);
        }

        return $datas ?? [];
    }


    public static function insert_regions() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'boat_regions';

        $url = 'https://www.booking-manager.com/api/v2/worldRegions';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer " . self::cread(),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $datas = json_decode($response, true);

        if (empty($datas)) {
            error_log('❌ Booking Manager API returned empty regions data.');
            return false;
        }

        // Insert into database
        foreach ($datas as $data) {   // <-- removed ['regions']
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $result = $wpdb->replace($table_name, [
                'id' => $id,
                'regions_data' => $json_data,
            ]);

            if ($result === false) {
                error_log('❌ Insert failed for region id: ' . $id . ' | ' . $wpdb->last_error);
            }
        }

        return $datas;
    }

    public static function insert_companies() {

		global $wpdb;
        $table_name = $wpdb->prefix . 'boat_companies';

        $url = 'https://www.booking-manager.com/api/v2/companies';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer " . self::cread(),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $datas = json_decode($response, true);

		if (empty($datas)) {
            error_log('Booking Manager API returned empty company data.');
            return false;
        }

		// Insert into database
        foreach ($datas as $data) {
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $wpdb->replace($table_name, [
                'id' => $id,
                'company_data' => $json_data,
            ]);
        }

        return $datas ?? [];
    }

    /**
     * Cron entry point to sync boats company-wise
     */
    public static function process_boat_sync() {
        global $wpdb;

        error_log('✅ bl_boat_sync_cron fired');

        // Prevent overlapping cron runs
        if (get_transient('bl_boat_sync_lock')) {
            return;
        }
        set_transient('bl_boat_sync_lock', 1, 300); // lock 5 min

        // Fetch all available yacht IDs
        $yacht_ids = $wpdb->get_col("SELECT yacht_id FROM {$wpdb->prefix}yacht_availability");

        if (empty($yacht_ids)) {
            wp_clear_scheduled_hook('bl_boat_sync_cron');
            delete_transient('bl_boat_sync_lock');
            error_log('🎉 No yachts available. Cron stopped.');
            return;
        }

        // Process each yacht ID
        foreach ($yacht_ids as $yacht_id) {
            $result = self::insert_boat_by_yacht_id($yacht_id); // call same insert_boats method

            // Log result in the same way
            if ($result['error']) {
                error_log("❌ Yacht {$yacht_id} sync failed: {$result['error']}");
            } else {
                error_log("✅ Yacht {$yacht_id} synced successfully ({$result['inserted']} inserted).");
            }
        }

        delete_transient('bl_boat_sync_lock');
    }


    /**
     * Insert boats for a company from API
     */
    /**
     * Insert or update boat by yacht ID
     */
    public static function insert_boat_by_yacht_id($yacht_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'boats';
        $url   = "https://www.booking-manager.com/api/v2/yacht/{$yacht_id}";

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "accept: application/json",
                "Authorization: Bearer " . self::cread(),
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);

            $response  = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($http_code !== 200 || !$response) {
                return ['inserted' => 0, 'error' => "API error {$http_code}"];
            }

            $boat = json_decode($response, true);
            if (empty($boat) || !is_array($boat)) {
                return ['inserted' => 0, 'error' => 'Empty or invalid API response'];
            }

            $products = $boat['products'] ?? [];

            $filtered_products = [];

            if (is_array($products)) {
                foreach ($products as $product) {
                    $filtered_products[] = [
                        'name'              => $product['name'] ?? '',
                    ];
                }
            }

            // Duplicate check
            $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE id = %d", $yacht_id));
            if ($exists) return ['inserted' => 0, 'error' => null];

            $filtered = [
                'id'                => $yacht_id,
                'name'              => $boat['name'] ?? '',
                'model'             => $boat['model'] ?? '',
                'modelId'           => $boat['modelId'] ?? null,

                // Company & location
                'companyId'         => $boat['companyId'] ?? null,
                'company'           => $boat['company'] ?? '',
                'homeBaseId'        => $boat['homeBaseId'] ?? null,
                'homeBase'          => $boat['homeBase'] ?? '',

                // Yacht details
                'year'              => $boat['year'] ?? null,
                'yearNote'          => $boat['yearNote'] ?? '',
                'kind'              => $boat['kind'] ?? '',
                'length'            => $boat['length'] ?? null,
                'beam'              => $boat['beam'] ?? null,
                'draught'           => $boat['draught'] ?? null,

                // Capacities
                'cabins'            => $boat['cabins'] ?? null,
                'berths'            => $boat['berths'] ?? null,
                'wc'                => $boat['wc'] ?? null,
                'maxPeopleOnBoard'  => $boat['maxPeopleOnBoard'] ?? $boat['berths'] ?? 0,

                // Technical
                'engine'            => $boat['engine'] ?? '',
                'fuelCapacity'      => $boat['fuelCapacity'] ?? null,
                'waterCapacity'     => $boat['waterCapacity'] ?? null,

                // Money
                'currency'          => $boat['currency'] ?? 'EUR',
                'deposit'           => $boat['deposit'] ?? 0,
                'depositWithWaiver' => $boat['depositWithWaiver'] ?? 0,
                'commissionPercentage' => $boat['commissionPercentage'] ?? 0,
                'maxDiscountFromCommissionPercentage'
                => $boat['maxDiscountFromCommissionPercentage'] ?? 0,

                // Charter rules
                'defaultCheckInTime'  => $boat['defaultCheckInTime'] ?? '',
                'defaultCheckOutTime' => $boat['defaultCheckOutTime'] ?? '',
                'defaultCheckInDay'   => $boat['defaultCheckInDay'] ?? null,
                'allCheckInDays'      => $boat['allCheckInDays'] ?? [],
                'minimumCharterDuration' => $boat['minimumCharterDuration'] ?? 0,
                'maximumCharterDuration' => $boat['maximumCharterDuration'] ?? 0,

                // Media
                'images'            => $boat['images'] ?? [],

                //Products
                'products'            => $filtered_products ?? [],
            ];

            $wpdb->insert($table, [
                'id'   => $yacht_id,
                'data' => wp_json_encode($filtered, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ]);

            return ['inserted' => 1, 'error' => null];

        } catch (\Throwable $e) {
            return ['inserted' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Insert Category
     */
    public static function insert_category() {

        global $wpdb;
        $table_name = $wpdb->prefix . 'boat_category';

        $api_url = 'https://www.booking-manager.com/api/v2/yachtTypes';

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer " . self::cread(),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $datas = json_decode($response, true);

        if (empty($datas) || !is_array($datas)) {
            return false;
        }

        foreach ($datas as $data) {

            $name = sanitize_text_field($data['name']);
            $json_data = wp_json_encode(['name' => $name]);

            // REPLACE with auto increment id → will insert new row
            $wpdb->replace(
                $table_name,
                [
                    'name'          => $name,
                    'category_data' => $json_data,
                ],
                [
                    '%s', // name
                    '%s', // category_data
                ]
            );
        }

        return true;
    }

    public static function insert_charterbases() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'boat_charterbaese';

        $api_url = 'https://www.booking-manager.com/api/v2/bases';

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "accept: application/json",
            "Authorization: Bearer " . self::cread(),
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $datas = json_decode($response, true);

        if (empty($datas)) {
            error_log('❌ Booking Manager API returned empty Charter Bases data.');
            return false;
        }

        foreach ($datas as $data) {
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $result = $wpdb->replace($table_name, [
                'id' => $id,
                'base_data' => $json_data,
            ]);

            if ($result === false) {
                error_log('❌ Insert failed for base id: ' . $id . ' | ' . $wpdb->last_error);
            }
        }

        error_log('✅ Charter bases inserted successfully: ' . count($datas));
        return true;
    }

    public static function sync_yacht_availability() {
        global $wpdb;

        error_log('⏰ Daily yacht availability sync started');

        // prevent overlap
        if (get_transient('bl_availability_lock')) {
            return;
        }
        set_transient('bl_availability_lock', 1, 900); // 15 min lock

        $current_year = date('Y');

        // API URL with only year
        $url = "https://www.booking-manager.com/api/v2/shortAvailability/{$current_year}?format=1";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "accept: application/json",
                "Authorization: Bearer " . self::cread(),
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $rows = json_decode($response, true);

        foreach ($rows as $row) {
            $wpdb->replace(
                "{$wpdb->prefix}yacht_availability",
                [
                    'yacht_id'            => (int) $row['y'],
                    'year'                => $current_year,
                    'availability_string' => $row['bs'],
                    'updated_at'          => current_time('mysql'),
                ]
            );
        }

        delete_transient('bl_availability_lock');
        error_log('✅ Daily yacht availability sync finished');
    }
}