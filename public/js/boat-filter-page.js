jQuery(document).ready(function($) {

    // Global variable to store current AJAX request for cancellation
    var currentAjaxRequest = null;
    var requestRetryCount = 0;
    var maxRetries = 3;

    // Add loading state styles for buttons
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .loading {
                opacity: 0.6;
                pointer-events: none;
                position: relative;
            }
            .loading:after {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 20px;
                height: 20px;
                margin: -10px 0 0 -10px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #3498db;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                z-index: 9999;
            }
            .pagination-loading {
                opacity: 0.7;
                position: relative;
                background-color: #e3f2fd !important;
            }
            .pagination-loading:after {
                content: '⟳';
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                color: #2196f3;
                font-size: 14px;
                animation: spin 1s linear infinite;
                z-index: 1;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `)
        .appendTo('head');

    // Global variables to track API parameters
    var lastApiParams = {
        country: '',
        productName: '',
        dateFrom: '',
        dateTo: ''
    };

    // Global AJAX boat loader (no page reload)
    window.loadBoatsAjax = function(paged, source, forceApiCall) {
        paged = paged || 1;
        source = source || 'auto'; // 'filter', 'pagination', or 'auto'
        forceApiCall = forceApiCall || false;

        // Cancel any existing request to prevent conflicts
        if (currentAjaxRequest && currentAjaxRequest.readyState !== 4) {
            currentAjaxRequest.abort();
            currentAjaxRequest = null;
        }

        $('.boat-lists-loader').fadeIn();

        // Read current URL params
        var urlParams = new URLSearchParams(window.location.search);
        var currentApiParams = {
            country: urlParams.get('country') || '',
            productName: urlParams.get('productName') || '',
            dateFrom: urlParams.get('dateFrom') || '',
            dateTo: urlParams.get('dateTo') || ''
        };

        // Check if API parameters have changed
        var apiParamsChanged = forceApiCall ||
            currentApiParams.country !== lastApiParams.country ||
            currentApiParams.productName !== lastApiParams.productName ||
            currentApiParams.dateFrom !== lastApiParams.dateFrom ||
            currentApiParams.dateTo !== lastApiParams.dateTo;

        console.log('🔍 API Parameters Check:', {
            current: currentApiParams,
            last: lastApiParams,
            changed: apiParamsChanged,
            source: source,
            hasDates: !!(currentApiParams.dateFrom && currentApiParams.dateTo)
        });

        // Always use API approach if dates are present OR if API parameters changed
        var shouldUseApi = apiParamsChanged || (currentApiParams.dateFrom && currentApiParams.dateTo);

        if (shouldUseApi) {
            if (apiParamsChanged) {
                console.log('📡 API parameters changed - calling Booking Manager API');
            } else {
                console.log('📡 Dates present - using API + local filtering approach');
            }

            lastApiParams = {...currentApiParams}; // Update last known API params

            // Build AJAX URL with GET params so PHP $_GET sees them
            var ajaxUrl = nausys_ajax_obj.ajax_url;
            var qs = [];
            if (currentApiParams.country) qs.push('country=' + encodeURIComponent(currentApiParams.country));
            if (currentApiParams.productName) qs.push('productName=' + encodeURIComponent(currentApiParams.productName));
            if (currentApiParams.dateFrom) qs.push('dateFrom=' + encodeURIComponent(currentApiParams.dateFrom));
            if (currentApiParams.dateTo) qs.push('dateTo=' + encodeURIComponent(currentApiParams.dateTo));
            if (qs.length) ajaxUrl += '?' + qs.join('&');

            // AJAX call to get API data + local filtering
            var ajaxData = {
                action: 'bl_get_paginated_boats',
                paged: paged,
                charter_type: $('#charterType').val(),
                free_yacht: $('#dateRange').val(),
                model: '',
                company: '',
                location: $('#country').val(),
                cabin: $('#cabin').val(),
                person: $('#people').val(),
                year: $('#year').val(),
                category: currentApiParams.productName
                // Note: NO local_filters_only flag when dates are present
            };

            makeAjaxRequest(ajaxUrl, ajaxData, paged, source);

        } else {
            console.log('🏠 No dates - using local-only filtering as fallback');

            // Only use local-only filtering when NO dates are present
            applyLocalFiltersOnly(paged);
        }
    };

    // Function to apply only local filters without API call (only when NO dates)
    function applyLocalFiltersOnly(paged) {
        // Check if dates are present - if so, should not use local-only
        var dateFrom = $('#dateFrom').val();
        var dateTo = $('#dateTo').val();

        if (dateFrom && dateTo) {
            console.log('⚠️ Dates present but applyLocalFiltersOnly called - this should not happen');
            console.log('🔄 Redirecting to full API + local filtering instead');
            loadBoatsAjax(paged, 'filter_redirect', false);
            return;
        }

        // Get current local filter values
        var localFilters = {
            charterType: $('#charterType').val(),
            person: $('#people').val(),
            cabin: $('#cabin').val(),
            year: $('#year').val()
        };

        console.log('🔧 Applying local filters only (no dates):', localFilters);

        // Make AJAX call but with flag to use local-only search
        var ajaxData = {
            action: 'bl_get_paginated_boats',
            paged: paged,
            local_filters_only: true, // Only when no dates present
            charter_type: localFilters.charterType,
            person: localFilters.person,
            cabin: localFilters.cabin,
            year: localFilters.year
        };

        makeAjaxRequest(nausys_ajax_obj.ajax_url, ajaxData, paged, 'local_filter');
    }

    // Centralized AJAX request function
    function makeAjaxRequest(ajaxUrl, ajaxData, paged, source) {
        currentAjaxRequest = $.ajax({
            url: ajaxUrl,
            type: 'POST',
            timeout: 60000,
            data: ajaxData,
            beforeSend: function() {
                if (source === 'filter' || source === 'local_filter') {
                    $('#submitFilter').prop('disabled', true).addClass('loading');
                }
            },
            success: function(res) {
                requestRetryCount = 0;
                $('.boat-lists-loader').fadeOut();

                if (res.success && res.data) {
                    $('.boat-lists').html(res.data.boats_html);
                    $('.boat-listing-pagi').html(res.data.pagination_html);
                    var total = res.data.total_boats || 0;
                    var message = total > 0 ? '🔍 ' + total + ' boat' + (total > 1 ? 's' : '') + ' found' : '🚫 No boats found';
                    $('#boat-count').text(message);
                } else {
                    $('.boat-lists').html('<p>No boats found.</p>');
                    $('#boat-count').text('🚫 No boats found');
                }
            },
            error: function(xhr, status, error) {
                $('.boat-lists-loader').fadeOut();

                if (status === 'abort') return;

                if (status === 'timeout' && requestRetryCount < maxRetries) {
                    requestRetryCount++;
                    setTimeout(function() {
                        makeAjaxRequest(ajaxUrl, ajaxData, paged, source);
                    }, 2000);
                    return;
                }

                $('.boat-lists').html('<p>❌ Error loading boats. Please try again.</p>');
                $('#boat-count').text('❌ Error');
                requestRetryCount = 0;
            },
            complete: function() {
                if (source === 'filter' || source === 'local_filter') {
                    $('#submitFilter').prop('disabled', false).removeClass('loading');
                }
                $('.bl-page').removeClass('pagination-loading');
                currentAjaxRequest = null;
            }
        });
    }

    // Populate inputs from URL params (essential + optional)
    function populateFiltersFromUrl() {
        var urlParams = new URLSearchParams(window.location.search);

        // Essential parameters (always check these)
        if (urlParams.get('country')) {
            $('#country').val(urlParams.get('country')).trigger('change.select2');
        }

        if (urlParams.get('productName')) {
            $('#productName').val(urlParams.get('productName')).trigger('change.select2');
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

            // Also set the dates in flatpickr to highlight them in calendar
            setTimeout(function() {
                var dateRangeElement = document.getElementById('dateRange');
                if (dateRangeElement && dateRangeElement._flatpickr) {
                    // Convert ISO dates to Date objects
                    var startDate = new Date(dateFrom);
                    var endDate = new Date(dateTo);

                    // Set the selected dates in flatpickr
                    dateRangeElement._flatpickr.setDate([startDate, endDate], false);

                    console.log('📅 Highlighted dates in calendar:', displayDate);
                }
            }, 500); // Give flatpickr time to initialize
        }

        // Optional parameters (only if they exist - coming from filter page)
        if (urlParams.get('charterType')) {
            $('#charterType').val(urlParams.get('charterType')).trigger('change.select2');
        }

        if (urlParams.get('person')) {
            $('#people').val(urlParams.get('person')).trigger('change.select2');
        }

        if (urlParams.get('cabin')) {
            $('#cabin').val(urlParams.get('cabin')).trigger('change.select2');
        }

        if (urlParams.get('year')) {
            $('#year').val(urlParams.get('year')).trigger('change.select2');
        }

        // Initialize lastApiParams from URL on first load
        lastApiParams = {
            country: urlParams.get('country') || '',
            productName: urlParams.get('productName') || '',
            dateFrom: urlParams.get('dateFrom') || '',
            dateTo: urlParams.get('dateTo') || ''
        };

        console.log('🔧 Initialized lastApiParams from URL:', lastApiParams);
    }

    // Filter button click (AJAX + URL update, no reload)
    $('#submitFilter').on('click', function() {
        console.log('🔍 Filter button clicked');

        // Get current URL params to compare
        var currentUrlParams = new URLSearchParams(window.location.search);
        var currentApiParams = {
            country: currentUrlParams.get('country') || '',
            productName: currentUrlParams.get('productName') || '',
            dateFrom: currentUrlParams.get('dateFrom') || '',
            dateTo: currentUrlParams.get('dateTo') || ''
        };

        // Get new values from form
        var newApiParams = {
            country: $('#country').val() || '',
            productName: $('#productName').val() || '',
            dateFrom: '',
            dateTo: ''
        };

        // Handle date range
        var dateRange = $('#dateRange').val();
        if (dateRange) {
            var parts = [];
            if (dateRange.indexOf(' to ') !== -1) {
                parts = dateRange.split(' to ');
            } else if (dateRange.indexOf(' - ') !== -1) {
                parts = dateRange.split(' - ');
            }

            if (parts.length === 2) {
                function toIsoFormat(dateStr) {
                    var d = dateStr.trim().split('.');
                    if (d.length === 3) {
                        return d[2] + '-' + d[1] + '-' + d[0] + 'T00:00:00';
                    }
                    return '';
                }

                newApiParams.dateFrom = toIsoFormat(parts[0]);
                newApiParams.dateTo = toIsoFormat(parts[1]);
            }
        }

        // Check if API parameters will change
        var apiParamsWillChange =
            newApiParams.country !== currentApiParams.country ||
            newApiParams.productName !== currentApiParams.productName ||
            newApiParams.dateFrom !== currentApiParams.dateFrom ||
            newApiParams.dateTo !== currentApiParams.dateTo;

        console.log('🔍 API Parameter Change Check:', {
            current: currentApiParams,
            new: newApiParams,
            willChange: apiParamsWillChange
        });

        // Start with ALL existing URL parameters to preserve everything
        var params = [];

        // Preserve all existing URL parameters (including empty ones for API filtering)
        for (var [key, value] of currentUrlParams.entries()) {
            params.push(key + '=' + encodeURIComponent(value || ''));
        }

        // Function to update or add parameter (always include for API filtering)
        function updateParam(paramName, newValue) {
            // Always remove existing parameter first
            params = params.filter(function(p) {
                return !p.startsWith(paramName + '=');
            });

            // Always add parameter (empty values are meaningful for API filtering)
            params.push(paramName + '=' + encodeURIComponent(newValue || ''));
            return true;
        }

        // Update parameters from form fields
        updateParam('country', newApiParams.country);
        updateParam('productName', newApiParams.productName);
        updateParam('dateFrom', newApiParams.dateFrom);
        updateParam('dateTo', newApiParams.dateTo);

        // Local filter parameters
        updateParam('charterType', $('#charterType').val());

        var person = $('#people').val();
        if (person && !isNaN(person) && parseInt(person) > 0 && parseInt(person) <= 50) {
            updateParam('person', person);
        }

        var cabin = $('#cabin').val();
        if (cabin && !isNaN(cabin) && parseInt(cabin) > 0 && parseInt(cabin) <= 20) {
            updateParam('cabin', cabin);
        }

        var year = $('#year').val();
        if (year && !isNaN(year) && parseInt(year) >= 1950 && parseInt(year) <= 2030) {
            updateParam('year', year);
        }

        // Check for any additional form fields that might exist
        var additionalFields = [
            {field: '#berths', param: 'berths'},
            {field: '#wc', param: 'wc'},
            {field: '#minLength', param: 'minLength'},
            {field: '#maxLength', param: 'maxLength'},
            {field: '#companyId', param: 'companyId'},
            {field: '#baseFromId', param: 'baseFromId'},
            {field: '#baseToId', param: 'baseToId'},
            {field: '#sailingAreaId', param: 'sailingAreaId'},
            {field: '#modelId', param: 'modelId'},
            {field: '#flexibility', param: 'flexibility'}
        ];

        additionalFields.forEach(function(filter) {
            var value = $(filter.field).val();
            if (value && value.trim() !== '') {
                updateParam(filter.param, value);
            }
        });

        // Build final URL with all preserved and updated parameters
        var newUrl = '/boat/boat-filter' + (params.length ? '?' + params.join('&') : '');
        console.log('🔗 Final URL with ALL parameters:', newUrl);

        // Update browser URL without page reload
        window.history.pushState({}, '', newUrl);

        // Load boats via AJAX (force API call if main params changed)
        console.log('📡 Calling loadBoatsAjax with forceApiCall:', apiParamsWillChange);
        loadBoatsAjax(1, 'filter', apiParamsWillChange);
    });


    // Pagination clicks (AJAX only, no page reload)
    $(document).on('click', '.bl-page', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        var $clickedButton = $(this);

        // Add temporary visual feedback to clicked pagination button
        $clickedButton.addClass('pagination-loading');

        // Remove loading class after a short delay (will be removed when results load)
        setTimeout(function() {
            $clickedButton.removeClass('pagination-loading');
        }, 3000);

        // console.log('📄 Pagination clicked: page ' + page);
        loadBoatsAjax(page, 'pagination'); // AJAX call, no page reload
    });

    // Initialize on page load
    populateFiltersFromUrl();

    // Auto-load boats if URL has main API params, otherwise show message for missing dates
    var urlParams = new URLSearchParams(window.location.search);
    var hasCountry = urlParams.get('country');
    var hasProductName = urlParams.get('productName');
    var hasDateFrom = urlParams.get('dateFrom');
    var hasDateTo = urlParams.get('dateTo');

    if (hasCountry || hasProductName || (hasDateFrom && hasDateTo)) {
        // Has main filter parameters, load boats
        setTimeout(function() {
            loadBoatsAjax(1, 'auto', true); // Force API call on initial load
        }, 500);
    } else if (!hasDateFrom || !hasDateTo) {
        // Missing date parameters, show message
        console.log('🚫 No date parameters found in URL - showing no-boats-found message');
        setTimeout(function() {
            $('.boat-lists-loader').hide();
            $('.boat-lists').html('<div class="no-boats-found" style="text-align: center; padding: 40px; color: #666;"><h3>📅 Please select your travel dates to search for boats</h3><p>Use the date picker above to choose your desired travel dates and search for available boats.</p></div>');
            $('#boat-count').text('📅 Please select dates to search');
        }, 100);
    }
});