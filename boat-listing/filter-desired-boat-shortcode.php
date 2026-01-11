<?php
// Shortcode: [filter_desired_boat]
// Simplified home page filter with only essential fields

function filter_desired_boat_shortcode() {

    $helper = new Boat_Listing_Helper();
    $icon   = $helper->icons();

    $getBoatProductTypes = $helper->getBoatProductTypes();

    $data = $helper->sortPriorityCountries();
    $regionsOrdered     = $data['regions'];
    $remainingCountries = $data['countries'];
    $priorityCountries  = $data['priority'] ?? [];

    ob_start();
    ?>
    <div class="filter-desired-boat-container" style="background: #f8f9fa; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 1000px; margin: 0 auto;">
        <h2 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">🚤 Find Your Perfect Boat Charter</h2>

        <form id="filter-desired-boat-form"
              action="/boat/boat-filter"
              method="get"
              style="display: flex; gap: 20px; align-items: end; justify-content: center; flex-wrap: wrap;">

            <!-- Destination -->
            <div class="filter-fieldset" style="min-width: 280px;">
                <label for="country" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50; font-size: 16px;">
                    <?php echo $icon['location']; ?> Destination
                </label>

                <select name="country" id="country" class="boat-listing-select2"
                        style="width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; background: white;">
                    <option value="">Choose Your Destination</option>

                    <?php if (!empty($priorityCountries)): ?>
                        <optgroup label="🌟 Popular Destinations">
                            <?php foreach ($priorityCountries as $country): ?>
                                <option value="<?php echo esc_attr($country['country_data']['shortName']); ?>">
                                    <?php echo esc_html($country['country_data']['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>

                    <?php foreach ($regionsOrdered as $region):
                        $regionId   = $region['id'];
                        $regionName = $region['regions_data']['name'] ?? 'Unknown Region';

                        $regionCountries = array_filter($remainingCountries, function ($country) use ($regionId) {
                            return ($country['country_data']['worldRegion'] ?? null) == $regionId;
                        });

                        if (empty($regionCountries)) continue;
                        ?>
                        <optgroup label="🌍 <?php echo esc_html($regionName); ?>">
                            <?php foreach ($regionCountries as $country): ?>
                                <option value="<?php echo esc_attr($country['country_data']['shortName']); ?>">
                                    <?php echo esc_html($country['country_data']['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Charter Type / Product Name -->
            <div class="filter-fieldset" style="min-width: 220px;">
                <label for="productName" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50; font-size: 16px;">
                    <?php echo $icon['category']; ?> Charter Type
                </label>

                <select name="productName" id="productName" class="boat-listing-select2"
                        style="width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; background: white;">
                    <option value="">Choose Charter Type</option>
                    <?php foreach ($getBoatProductTypes as $productType): ?>
                        <option value="<?php echo esc_attr($productType); ?>">
                            ⛵ <?php echo esc_html($productType); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date Range -->
            <div class="filter-fieldset" style="min-width: 250px;">
                <label for="dateRange" style="display: block; font-weight: 600; margin-bottom: 8px; color: #2c3e50; font-size: 16px;">
                    <?php echo $icon['date']; ?> Travel Dates
                </label>
                <input type="text" id="dateRange" name="dateRange"
                       placeholder="Select your travel dates"
                       autocomplete="off"
                       style="width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; background: white;"
                       class="boat-listing-input-text bl-filter-desired-date-range-picker" />
            </div>

            <!-- Hidden API fields for form processing -->
            <input type="hidden" name="dateFrom" id="dateFrom">
            <input type="hidden" name="dateTo" id="dateTo">

            <!-- Hidden fields for all filter parameters -->
            <input type="hidden" name="charterType" id="hidden_charterType" value="">
            <input type="hidden" name="person" id="hidden_person" value="">
            <input type="hidden" name="cabin" id="hidden_cabin" value="">
            <input type="hidden" name="year" id="hidden_year" value="">

            <!-- Search Button -->
            <div class="filter-fieldset">
                <button type="submit"
                        style="background: linear-gradient(135deg, #3498db, #2980b9);
                               color: white;
                               padding: 17px 45px;
                               border: none;
                               border-radius: 8px;
                               font-size: 18px;
                               font-weight: 600;
                               cursor: pointer;
                               transition: all 0.3s ease;
                               box-shadow: 0 4px 15px rgba(52, 152, 219, 0.4);
                               min-width: 160px;"
                        onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(52, 152, 219, 0.6)';"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(52, 152, 219, 0.4)';">
                    🔍 Search Boats
                </button>
            </div>
        </form>
    </div>
    <?php

    return ob_get_clean();
}

add_shortcode('filter_desired_boat', 'filter_desired_boat_shortcode');

?>