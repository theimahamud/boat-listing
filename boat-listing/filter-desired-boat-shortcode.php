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
                    <?php echo $icon['location']; ?> Destination <span style="color: #e74c3c;">*</span>
                </label>

                <select name="country" id="country" class="boat-listing-select2" required
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
                    <?php echo $icon['category']; ?> Charter Type <span style="color: #e74c3c;">*</span>
                </label>

                <select name="productName" id="productName" class="boat-listing-select2" required
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
                    <?php echo $icon['date']; ?> Travel Dates <span style="color: #e74c3c;">*</span>
                </label>
                <input type="text" id="dateRange" name="dateRange"
                       placeholder="Select your travel dates"
                       autocomplete="off" required
                       style="width: 100%; padding: 15px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; background: white;"
                       class="boat-listing-input-text bl-date-range-picker" />
            </div>

            <!-- Hidden API fields for form processing -->
            <input type="hidden" name="dateFrom" id="dateFrom">
            <input type="hidden" name="dateTo" id="dateTo">

            <!-- Hidden fields for all filter parameters -->
            <input type="hidden" name="charterType" id="hidden_charterType" value="">
            <input type="hidden" name="person" id="hidden_person" value="">
            <input type="hidden" name="cabin" id="hidden_cabin" value="">
            <input type="hidden" name="year" id="hidden_year" value="">
            <input type="hidden" name="berths" id="hidden_berths" value="">
            <input type="hidden" name="wc" id="hidden_wc" value="">
            <input type="hidden" name="minLength" id="hidden_minLength" value="">
            <input type="hidden" name="maxLength" id="hidden_maxLength" value="">
            <input type="hidden" name="companyId" id="hidden_companyId" value="">
            <input type="hidden" name="baseFromId" id="hidden_baseFromId" value="">
            <input type="hidden" name="baseToId" id="hidden_baseToId" value="">
            <input type="hidden" name="sailingAreaId" id="hidden_sailingAreaId" value="">
            <input type="hidden" name="modelId" id="hidden_modelId" value="">
            <input type="hidden" name="flexibility" id="hidden_flexibility" value="1">

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

    <script>
    jQuery(document).ready(function($) {

        // Simple form submission - only essential parameters
        $('#filter-desired-boat-form').on('submit', function(e) {
            e.preventDefault();

            console.log('🏠 Simple boat search form submitted');

            var params = [];

            // Get only the essential fields
            var country = $('#country').val();
            var productName = $('#productName').val();
            var dateRange = $('#dateRange').val();

            console.log('Form values:', {
                country: country,
                productName: productName,
                dateRange: dateRange
            });

            // Validate required fields
            if (!country) {
                alert('🌍 Please select a destination');
                $('#country').focus();
                return false;
            }

            if (!productName) {
                alert('⛵ Please select a charter type');
                $('#productName').focus();
                return false;
            }

            if (!dateRange) {
                alert('📅 Please select your travel dates');
                $('#dateRange').focus();
                return false;
            }

            // Add core parameters
            params.push('country=' + encodeURIComponent(country));
            params.push('productName=' + encodeURIComponent(productName));

            // Add all hidden field parameters (if they have values)
            var hiddenFields = [
                'charterType', 'person', 'cabin', 'year', 'berths', 'wc',
                'minLength', 'maxLength', 'companyId', 'baseFromId',
                'baseToId', 'sailingAreaId', 'modelId', 'flexibility'
            ];

            // Add all hidden field parameters (essential for API filtering)
            var hiddenFields = [
                'charterType', 'person', 'cabin', 'year', 'berths', 'wc',
                'minLength', 'maxLength', 'companyId', 'baseFromId',
                'baseToId', 'sailingAreaId', 'modelId', 'flexibility'
            ];

            hiddenFields.forEach(function(fieldName) {
                var fieldValue = $('#hidden_' + fieldName).val();
                // Include all parameters for API filtering (empty values are meaningful for API)
                params.push(fieldName + '=' + encodeURIComponent(fieldValue || ''));
                if (fieldValue && fieldValue.trim() !== '') {
                    console.log('  - Added hidden field ' + fieldName + ': ' + fieldValue);
                }
            });

            console.log('🔍 Hidden fields being passed:', hiddenFields.filter(function(field) {
                return $('#hidden_' + field).val();
            }));

            // Handle date range conversion to API format
            if (dateRange) {
                var parts = [];
                if (dateRange.indexOf(' to ') !== -1) {
                    parts = dateRange.split(' to ');
                } else if (dateRange.indexOf(' - ') !== -1) {
                    parts = dateRange.split(' - ');
                }

                if (parts.length === 2) {
                    // Convert DD.MM.YYYY to YYYY-MM-DDTHH:mm:ss
                    function toIsoFormat(dateStr) {
                        var d = dateStr.trim().split('.');
                        if (d.length === 3) {
                            return d[2] + '-' + d[1] + '-' + d[0] + 'T00:00:00';
                        }
                        return '';
                    }

                    var dateFrom = toIsoFormat(parts[0]);
                    var dateTo = toIsoFormat(parts[1]);

                    if (dateFrom && dateTo) {
                        params.push('dateFrom=' + encodeURIComponent(dateFrom));
                        params.push('dateTo=' + encodeURIComponent(dateTo));
                    }
                }
            }

            // Build clean URL with only essential parameters
            var targetUrl = '/boat/boat-filter';
            if (params.length > 0) {
                targetUrl += '?' + params.join('&');
            }

            console.log('🔗 Redirecting to: ' + targetUrl);
            console.log('📋 Essential parameters only:', params);

            // Redirect to filter page
            window.location.href = targetUrl;
        });

        // Add some visual feedback for form interaction
        $('#country, #productName').on('change', function() {
            if ($(this).val()) {
                $(this).css('border-color', '#27ae60');
            } else {
                $(this).css('border-color', '#ddd');
            }
        });

        $('#dateRange').on('change', function() {
            if ($(this).val()) {
                $(this).css('border-color', '#27ae60');
            } else {
                $(this).css('border-color', '#ddd');
            }
        });

        // Function to populate hidden fields from URL parameters
        function populateHiddenFieldsFromUrl() {
            var urlParams = new URLSearchParams(window.location.search);

            console.log('🔄 Checking URL for existing filter parameters...');

            // Update hidden fields with URL parameters
            var hiddenFields = [
                'charterType', 'person', 'cabin', 'year', 'berths', 'wc',
                'minLength', 'maxLength', 'companyId', 'baseFromId',
                'baseToId', 'sailingAreaId', 'modelId', 'flexibility'
            ];

            hiddenFields.forEach(function(fieldName) {
                var urlValue = urlParams.get(fieldName);
                // Set the value from URL (including empty for API consistency)
                $('#hidden_' + fieldName).val(urlValue || '');
                if (urlValue && urlValue.trim() !== '') {
                    console.log('  - Updated hidden field ' + fieldName + ': ' + urlValue);
                }
            });

            // Also populate visible fields if they exist in URL
            if (urlParams.get('country')) {
                $('#country').val(urlParams.get('country'));
            }
            if (urlParams.get('productName')) {
                $('#productName').val(urlParams.get('productName'));
            }
            if (urlParams.get('dateFrom') && urlParams.get('dateTo')) {
                var dateFrom = urlParams.get('dateFrom').replace('T00:00:00', '');
                var dateTo = urlParams.get('dateTo').replace('T00:00:00', '');

                function formatDateForDisplay(isoDate) {
                    var parts = isoDate.split('-');
                    return parts[2] + '.' + parts[1] + '.' + parts[0];
                }

                var displayDate = formatDateForDisplay(dateFrom) + ' to ' + formatDateForDisplay(dateTo);
                $('#dateRange').val(displayDate);
            }
        }

        // Populate hidden fields when page loads (in case user came back from filter page)
        populateHiddenFieldsFromUrl();

    });
    </script>
    <?php

    return ob_get_clean();
}

add_shortcode('filter_desired_boat', 'filter_desired_boat_shortcode');

?>
