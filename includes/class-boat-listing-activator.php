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
		//self::insert_all_boats();
		// self::insert_companies();
		// self::insert_category();
		// self::insert_models();
		// self::insert_locaitons();
		// self::insert_charterbases();
		// self::insert_freeyacht();

	}

	public static function cread(){

        return get_option('bl_api_key');
    }

	public static function create_table (){

		global $wpdb;

		$boat_table = $wpdb->prefix . 'boats';
		$free_boat_table = $wpdb->prefix . 'boat_free';
		$models = $wpdb->prefix . 'boat_models';
		$company_table = $wpdb->prefix . 'boat_companies';
		$boat_category = $wpdb->prefix . 'boat_category';
		$location_table = $wpdb->prefix . 'boat_locations';
		$charterbase_table = $wpdb->prefix . 'boat_charterbaese';
		$book_reserve_table = $wpdb->prefix . 'boat_book_request';
        $boat_country_table = $wpdb->prefix . 'boat_country';
        $boat_countrystate_table = $wpdb->prefix . 'boat_country_state';
        $boat_regions_table = $wpdb->prefix . 'boat_regions';
        $boat_price_list_table = $wpdb->prefix . 'boat_price_list';
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

		$boat_countrystate = "CREATE TABLE $boat_countrystate_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
            country_state_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($boat_countrystate);

		$boat_regions = "CREATE TABLE $boat_regions_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
            regions_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($boat_regions);

		$boat_free = "CREATE TABLE $free_boat_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
			free_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($boat_free);

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

		$sql2 = "CREATE TABLE $models (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
			model_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($sql2);

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

		$boat_price_list = "CREATE TABLE $boat_price_list_table (
			id BIGINT(20) UNSIGNED NOT NULL PRIMARY KEY,
			price_data LONGTEXT NOT NULL
		) $charset_collate;";

		dbDelta($boat_price_list);

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

    /**
     * Insert Country State
     */
    public static function insert_country_state() {

		global $wpdb;
        $table_name = $wpdb->prefix . 'boat_country_state';

        $url = 'https://ws.nausys.com/CBMS-external/rest/catalogue/v6/countrystates';

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
        foreach ($datas['countries'] as $data) {
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $wpdb->replace($table_name, [
                'id' => $id,
                'country_state_data' => $json_data,
            ]);
        }

        return $datas['countries'] ?? [];
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
     * Insert Free yacht
     */
    public static function insert_freeyacht() {

        global $wpdb;

        $table_name = $wpdb->prefix . 'boat_free';
        $free_yacht_url = "https://ws.nausys.com/CBMS-external/rest/yachtReservation/v6/freeYachts";

        $helper = new Boat_Listing_Helper();
        $all_yacht_ids = $helper->fetch_boat_ids(); // ✅ fetch all IDs from helper


        if (empty($all_yacht_ids)) {
            return "No yacht IDs found.";
        }

        // -----------------------------------------------------
        // ⏰ Date Range (Tomorrow → Next 7 Days)
        // -----------------------------------------------------
        $periodFrom = date('d.m.Y', strtotime('+1 day'));
        $periodTo   = date('d.m.Y', strtotime('+7 days'));

        // -----------------------------------------------------
        // ⚙️ Process yachts in chunks
        // -----------------------------------------------------
        $chunks = array_chunk($all_yacht_ids, 100);
        
        $inserted_count = 0;

        foreach ($chunks as $chunk) {

            $free_payload = [
                "credentials" => [
                    "username" => get_option('bl_api_key'),
                ],
                "periodFrom" => $periodFrom,
                "periodTo"   => $periodTo,
                "yachts"     => array_map('intval', $chunk),
            ];

            $ch = curl_init($free_yacht_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "accept: application/json",
                "Authorization: Bearer " . self::cread(),
            ]);
            $response = curl_exec($ch);
            curl_close($ch);

            $datas = json_decode($response, true);

            if (empty($datas['freeYachts'])) {
                continue;
            }

            foreach ($datas['freeYachts'] as $free_yacht) {
                $yacht_id = $free_yacht['yachtId'] ?? null;
                if (!$yacht_id) continue;

                $wpdb->replace(
                    $table_name,
                    [
                        'id'        => $yacht_id,
                        'free_data' => wp_json_encode($free_yacht),
                        ],
                        [
                            '%d',
                            '%s',
                        ]
                    );

                $inserted_count++;
            }

            // Prevent API overload
            sleep(1);
        }

        return $inserted_count;
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
     * Insert price lists
     */
	public static function insert_price_list(){

		global $wpdb;
        $table_name = $wpdb->prefix . 'boat_price_list';

		$api_url = 'https://ws.nausys.com/CBMS-external/rest/catalogue/v6/priceLists';

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
            error_log('Booking Manager API returned empty model data.');
            return false;
        }

        foreach ($datas['priceLists'] as $data) {
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $wpdb->replace($table_name, [
                'id' => $id,
                'price_data' => $json_data,
            ]);
        }

        return true;
	}

    /**
     * Insert Models
     */
	public static function insert_models(){

		global $wpdb;
        $table_name = $wpdb->prefix . 'boat_models';

		$api_url = 'https://ws.nausys.com/CBMS-external/rest/catalogue/v6/yachtModels';

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
            error_log('Booking Manager API returned empty model data.');
            return false;
        }

        foreach ($datas['models'] as $data) {
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $wpdb->replace($table_name, [
                'id' => $id,
                'model_data' => $json_data,
            ]);
        }

        return true;
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

    /**
     * Insert Location
     */
	public static function insert_locaitons(){

		global $wpdb;
        $table_name = $wpdb->prefix . 'boat_locations';

		$api_url = 'https://ws.nausys.com/CBMS-external/rest/catalogue/v6/locations';

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
            error_log('Booking Manager API returned empty location data.');
            return false;
        }

        foreach ($datas['locations'] as $data) {
            $id = $data['id'];
            $json_data = wp_json_encode($data);

            $wpdb->replace($table_name, [
                'id' => $id,
                'location_data' => $json_data,
            ]);
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

        $year = $current_year + $i;

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
                    'year'                => $year,
                    'availability_string' => $row['bs'],
                    'updated_at'          => current_time('mysql'),
                ]
            );
        }

        delete_transient('bl_availability_lock');
        error_log('✅ Daily yacht availability sync finished');
    }
}