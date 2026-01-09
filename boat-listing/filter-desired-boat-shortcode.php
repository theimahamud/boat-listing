<?php
// Shortcode: [filter_desired_boat]
// Usage: Place [filter_desired_boat] on any page (e.g., homepage)

function filter_desired_boat_shortcode() {
    $helper = new Boat_Listing_Helper();
    $icon = $helper->icons();
    $regions = $helper->fetch_regions();
    $countries = $helper->fetch_country();
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
    <form id="filter-desired-boat-form" action="/boat/boat-filter" method="get" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
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
        <div class="filter-fieldset">
            <label for="search_free_yacht"><?php echo $icon['date']; ?> Dates </label>
            <input type="text" id="search_free_yacht" name="search_free_yacht" placeholder="Select date range" autocomplete="off" class="boat-listing-input-text bl-date-range-picker" />
        </div>
        <button type="submit">Search</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('filter_desired_boat', 'filter_desired_boat_shortcode');

