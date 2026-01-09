<?php

class BM_FreeBoatImporter {

    private $user     = "YOUR_EMAIL";
    private $pass     = "YOUR_PASSWORD";
    private $boat_tbl = "wp_bm_free_boats";
    private $company_tbl = "wp_bm_companies";

    public function run() {
        global $wpdb;

        $companies = $wpdb->get_col("SELECT company_id FROM {$this->company_tbl}");

        if (!$companies) {
            wp_die("No companies found");
        }

        foreach ($companies as $companyId) {
            $boats   = $this->fetch_boats($companyId);
            $booked  = $this->fetch_booked_boats($companyId);

            if (!$boats) continue;

            foreach ($boats as $boat) {
                $boatId = $boat['id'];

                if (isset($booked[$boatId])) {
                    continue; // skip booked
                }

                // insert free boat only once
                $exists = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT id FROM {$this->boat_tbl} WHERE boat_id = %d",
                        $boatId
                    )
                );

                if (!$exists) {
                    $wpdb->insert($this->boat_tbl, [
                        'boat_id'    => $boatId,
                        'company_id' => $companyId,
                        'name'       => $boat['name'] ?? '',
                        'model'      => $boat['model'] ?? '',
                        'created_at' => current_time('mysql')
                    ]);
                }
            }
        }

        wp_die("Free boats imported successfully!");
    }

    private function fetch_boats($companyId) {
        return $this->api_json("yachts?companyId=$companyId");
    }

    private function fetch_booked_boats($companyId) {
        $xml = $this->api_xml("reservations?companyId=$companyId");

        $booked = [];

        if (!$xml || empty($xml->reservation)) return $booked;

        foreach ($xml->reservation as $res) {
            if ((string)$res['blocksavailability'] === "1") {
                $id = (string)$res['resourceid'];
                $booked[$id] = true;
            }
        }

        return $booked;
    }

    private function api_json($endpoint) {
        $res = wp_remote_get("https://www.booking-manager.com/api/v2/$endpoint", [
            "headers" => [
                "Authorization" => "Basic " . base64_encode("$this->user:$this->pass")
            ],
            "timeout" => 30
        ]);

        if (is_wp_error($res)) return false;

        return json_decode(wp_remote_retrieve_body($res), true);
    }

    private function api_xml($endpoint) {
        $res = wp_remote_get("https://www.booking-manager.com/api/v2/$endpoint", [
            "headers" => [
                "Authorization" => "Basic " . base64_encode("$this->user:$this->pass")
            ],
            "timeout" => 30
        ]);

        if (is_wp_error($res)) return false;

        return simplexml_load_string(wp_remote_retrieve_body($res));
    }
}

// Run manually without cron
add_action('admin_init', function() {
    if (isset($_GET['import_free_boats'])) {
        (new BM_FreeBoatImporter())->run();
    }
});