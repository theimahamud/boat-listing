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

    // Global AJAX boat loader (no page reload)
    window.loadBoatsAjax = function(paged, source) {
        paged = paged || 1;
        source = source || 'auto'; // 'filter', 'pagination', or 'auto'
       // console.log('🚤 loadBoatsAjax called with page:', paged, 'source:', source);

        // Cancel any existing request to prevent conflicts
        if (currentAjaxRequest && currentAjaxRequest.readyState !== 4) {
           // console.log('⏹️ Cancelling previous request...');
            currentAjaxRequest.abort();
            currentAjaxRequest = null;
        }

        $('.boat-lists-loader').fadeIn();
        //$('#boat-count').text('🔄 Loading boats...');

        // Read current URL params
        var urlParams = new URLSearchParams(window.location.search);
        var country = urlParams.get('country') || '';
        var productName = urlParams.get('productName') || '';
        var dateFrom = urlParams.get('dateFrom') || '';
        var dateTo = urlParams.get('dateTo') || '';

        // console.log('📋 URL Params:', {
        //     country: country,
        //     productName: productName,
        //     dateFrom: dateFrom,
        //     dateTo: dateTo
        // });

        // Build AJAX URL with GET params so PHP $_GET sees them
        var ajaxUrl = nausys_ajax_obj.ajax_url;
        var qs = [];
        if (country) qs.push('country=' + encodeURIComponent(country));
        if (productName) qs.push('productName=' + encodeURIComponent(productName));
        if (dateFrom) qs.push('dateFrom=' + encodeURIComponent(dateFrom));
        if (dateTo) qs.push('dateTo=' + encodeURIComponent(dateTo));
        if (qs.length) ajaxUrl += '?' + qs.join('&');

       // console.log('🌐 AJAX URL:', ajaxUrl);

        // AJAX call (no page reload)
        var ajaxData = {
            action: 'bl_get_paginated_boats',
            paged: paged,
            charter_type: $('#charterType').val(),
            free_yacht: $('#dateRange').val(),
            model: '', // Not present in filter page
            company: '', // Not present in filter page
            location: $('#country').val(),
            cabin: $('#cabin').val(),
            person: $('#people').val(),
            year: $('#year').val(),
            category: productName
        };

       // console.log('📤 AJAX Data:', ajaxData);

        // Make the AJAX request with proper error handling and retry logic
        currentAjaxRequest = $.ajax({
            url: ajaxUrl,
            type: 'POST',
            timeout: 60000, // Increased to 60 seconds to match PHP timeout + buffer
            data: ajaxData,
            beforeSend: function() {
                // Only disable filter button when this is a filter operation
                if (source === 'filter') {
                    $('#submitFilter').prop('disabled', true).addClass('loading');
                   // console.log('📡 Starting filter API request...');
                } else {
                    //console.log('📡 Starting pagination API request...');
                }
            },
            success: function(res) {
                //console.log('✅ AJAX request successful');
                requestRetryCount = 0; // Reset retry counter on success
                $('.boat-lists-loader').fadeOut();

                if (res.success && res.data) {
                    // Update boat cards (same format as before)
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
                //console.log('❌ AJAX request failed:', status, error);

                $('.boat-lists-loader').fadeOut();

                if (status === 'abort') {
                   // console.log('⏹️ Request was cancelled (this is normal)');
                    return; // Don't show error for cancelled requests
                }

                if (status === 'timeout') {
                   // console.log('⏰ Request timed out');
                    if (requestRetryCount < maxRetries) {
                        requestRetryCount++;
                       // console.log('🔄 Retrying request (' + requestRetryCount + '/' + maxRetries + ')...');
                        setTimeout(function() {
                            loadBoatsAjax(paged, source);
                        }, 2000); // Retry after 2 seconds
                        return;
                    } else {
                        $('.boat-lists').html('<p>⏰ Request timed out. The server might be busy. Please try again.</p>');
                        $('#boat-count').text('�� Timeout');
                    }
                } else if (xhr.status === 0) {
                    //console.log('🔌 Network connection issue');
                    $('.boat-lists').html('<p>🔌 Network connection issue. Please check your connection and try again.</p>');
                    $('#boat-count').text('🔌 No connection');
                } else if (xhr.status >= 500) {
                   // console.log('🚨 Server error:', xhr.status);
                    $('.boat-lists').html('<p>🚨 Server error (' + xhr.status + '). Please try again later.</p>');
                    $('#boat-count').text('🚨 Server error');
                } else {
                   // console.log('❌ Unknown error:', xhr.status, error);
                    $('.boat-lists').html('<p>❌ Error loading boats. Please try again.</p>');
                    $('#boat-count').text('❌ Error');
                }

                requestRetryCount = 0; // Reset retry counter
            },
            complete: function() {
                // Only re-enable filter button if it was disabled (filter operation)
                if (source === 'filter') {
                    $('#submitFilter').prop('disabled', false).removeClass('loading');
                   // console.log('🏁 Filter AJAX request completed');
                } else {
                   // console.log('🏁 Pagination AJAX request completed');
                }

                // Always clear pagination loading states
                $('.bl-page').removeClass('pagination-loading');

                currentAjaxRequest = null;
            }
        });
    };

    // Populate inputs from URL params (essential + optional)
    function populateFiltersFromUrl() {
        var urlParams = new URLSearchParams(window.location.search);

        //console.log('🔍 Populating filters from URL - checking for essential and optional params:');

        // Essential parameters (always check these)
        if (urlParams.get('country')) {
           // console.log('  - Country: ' + urlParams.get('country'));
            $('#country').val(urlParams.get('country')).trigger('change.select2');
        }

        if (urlParams.get('productName')) {
           // console.log('  - ProductName: ' + urlParams.get('productName'));
            $('#productName').val(urlParams.get('productName')).trigger('change.select2');
        }

        if (urlParams.get('dateFrom') && urlParams.get('dateTo')) {
            var dateFrom = urlParams.get('dateFrom').replace('T00:00:00', '');
            var dateTo = urlParams.get('dateTo').replace('T00:00:00', '');

            //console.log('  - Dates: ' + dateFrom + ' to ' + dateTo);

            function formatDateForDisplay(isoDate) {
                var parts = isoDate.split('-');
                return parts[2] + '.' + parts[1] + '.' + parts[0];
            }

            var displayDate = formatDateForDisplay(dateFrom) + ' to ' + formatDateForDisplay(dateTo);
            $('#dateRange').val(displayDate);
        }

        // Optional parameters (only if they exist - coming from filter page)
        if (urlParams.get('charterType')) {
           // console.log('  - CharterType: ' + urlParams.get('charterType'));
            $('#charterType').val(urlParams.get('charterType')).trigger('change.select2');
        }

        if (urlParams.get('person')) {
           // console.log('  - Person: ' + urlParams.get('person'));
            $('#people').val(urlParams.get('person')).trigger('change.select2');
        }

        if (urlParams.get('cabin')) {
           // console.log('  - Cabin: ' + urlParams.get('cabin'));
            $('#cabin').val(urlParams.get('cabin')).trigger('change.select2');
        }

        if (urlParams.get('year')) {
          //  console.log('  - Year: ' + urlParams.get('year'));
            $('#year').val(urlParams.get('year')).trigger('change.select2');
        }

        // Count total parameters for debugging
        var totalParams = Array.from(urlParams.keys()).length;
        if (totalParams <= 4) {
            //console.log('✅ Simple search from home page (' + totalParams + ' params)');
        } else {
            //console.log('🔧 Advanced search with additional filters (' + totalParams + ' params)');
        }
    }

    // Filter button click (AJAX + URL update, no reload)
    $('#submitFilter').on('click', function() {
       // console.log('🔍 Filter button clicked');

        // Start with ALL existing URL parameters to preserve everything
        var currentUrlParams = new URLSearchParams(window.location.search);
        var params = [];

        //console.log('🔄 Preserving existing URL parameters...');

        // Preserve all existing URL parameters (including empty ones for API filtering)
        for (var [key, value] of currentUrlParams.entries()) {
            params.push(key + '=' + encodeURIComponent(value || ''));
            //console.log('  - Preserved: ' + key + '=' + (value || 'empty'));
        }

       // console.log('📝 Now checking form fields for updates...');

        // Function to update or add parameter (always include for API filtering)
        function updateParam(paramName, newValue) {
            // Always remove existing parameter first
            params = params.filter(function(p) {
                return !p.startsWith(paramName + '=');
            });

            // Always add parameter (empty values are meaningful for API filtering)
            params.push(paramName + '=' + encodeURIComponent(newValue || ''));
            if (newValue && newValue.trim() !== '') {
                //console.log('  - Updated: ' + paramName + '=' + newValue);
            } else {
              //  console.log('  - Updated: ' + paramName + '= (empty for API)');
            }
            return true;
        }

        // Update parameters from form fields (only if they have values)
        var country = $('#country').val();
        updateParam('country', country);

        var productName = $('#productName').val();
        updateParam('productName', productName);

        var charterType = $('#charterType').val();
        updateParam('charterType', charterType);

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

        // Handle date range updates
        var dateRange = $('#dateRange').val();
        if (dateRange) {
           // console.log('📅 Processing date range:', dateRange);
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

                var dateFrom = toIsoFormat(parts[0]);
                var dateTo = toIsoFormat(parts[1]);

                if (dateFrom && dateTo) {
                    updateParam('dateFrom', dateFrom);
                    updateParam('dateTo', dateTo);
                }
            }
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
        // console.log('🔗 Final URL with ALL parameters:', newUrl);
        // console.log('📊 Total parameters in URL:', params.length);

        // Update browser URL without page reload
        window.history.pushState({}, '', newUrl);

        // Load boats via AJAX (no page reload)
       // console.log('📡 Calling loadBoatsAjax with preserved parameters...');
        loadBoatsAjax(1, 'filter');
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

    // Auto-load boats if URL has params
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('country') || urlParams.get('productName') || urlParams.get('dateFrom')) {
        setTimeout(function() {
            loadBoatsAjax(1, 'auto');
        }, 500);
    }
});
