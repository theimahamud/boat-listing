jQuery(document).ready(function($) {

    // Configuration
    var DEBOUNCE_DELAY = 800; // Wait 800ms after last change before searching
    var REQUEST_TIMEOUT = 45000; // 45 seconds timeout
    var MAX_RETRIES = 2;

    // State management
    var currentAjaxRequest = null;
    var requestRetryCount = 0;
    var debounceTimer = null;
    var lastRequestParams = null;

    /**
     * Initialize date range picker with URL parameters
     */
    function initializeDateFromUrl() {
        console.log('🔄 Initializing dates from URL parameters...');

        var urlParams = new URLSearchParams(window.location.search);
        var dateFrom = urlParams.get('dateFrom');
        var dateTo = urlParams.get('dateTo');

        if (dateFrom && dateTo) {
           // console.log('📅 Found date parameters:', dateFrom, 'to', dateTo);

            // Convert ISO format back to display format (DD.MM.YYYY)
            function formatDateForDisplay(isoDate) {
                // Remove time part if present (2026-01-11T00:00:00 -> 2026-01-11)
                var dateOnly = isoDate.replace('T00:00:00', '').split('-');
                if (dateOnly.length === 3) {
                    return dateOnly[2] + '.' + dateOnly[1] + '.' + dateOnly[0]; // DD.MM.YYYY
                }
                return '';
            }

            // Convert ISO to JavaScript Date object for Flatpickr
            function isoToDate(isoString) {
                var cleanIso = isoString.replace('T00:00:00', '');
                var parts = cleanIso.split('-');
                if (parts.length === 3) {
                    return new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                }
                return null;
            }

            var fromDisplay = formatDateForDisplay(dateFrom);
            var toDisplay = formatDateForDisplay(dateTo);
            var fromDate = isoToDate(dateFrom);
            var toDate = isoToDate(dateTo);

            if (fromDisplay && toDisplay && fromDate && toDate) {
                var displayDate = fromDisplay + ' to ' + toDisplay;
                console.log('📅 Setting date range picker to:', displayDate);

                // Set the date range picker value
                var $dateRange = $('#dateRange');
                $dateRange.val(displayDate);

                // Also set the hidden fields
                $('#dateFrom').val(dateFrom);
                $('#dateTo').val(dateTo);

                // IMPORTANT: Set the Flatpickr instance dates for visual highlighting
                setTimeout(function() {
                    if ($dateRange[0] && $dateRange[0]._flatpickr) {
                        console.log('📅 Setting Flatpickr dates:', fromDate, 'to', toDate);
                        $dateRange[0]._flatpickr.setDate([fromDate, toDate], false);

                        // Add visual indication that dates are selected with enhanced styling
                        $dateRange.css({
                            'border-color': '#27ae60',
                            'background-color': '#f0fff0',
                            'box-shadow': '0 0 5px rgba(39, 174, 96, 0.3)'
                        });

                        console.log('✅ Date range and Flatpickr initialized successfully');
                    } else {
                        console.warn('⚠️ Flatpickr instance not found, retrying in 500ms...');

                        // Retry after Flatpickr is initialized
                        setTimeout(function() {
                            if ($dateRange[0] && $dateRange[0]._flatpickr) {
                                console.log('📅 Retry: Setting Flatpickr dates:', fromDate, 'to', toDate);
                                $dateRange[0]._flatpickr.setDate([fromDate, toDate], false);
                                $dateRange.css({
                                    'border-color': '#27ae60',
                                    'background-color': '#f0fff0',
                                    'box-shadow': '0 0 5px rgba(39, 174, 96, 0.3)'
                                });
                                console.log('✅ Date range initialized on retry');
                            } else {
                                console.error('❌ Flatpickr instance still not available');
                            }
                        }, 500);
                    }
                }, 100); // Small delay to ensure Flatpickr is initialized

                // Trigger change event to ensure any other handlers are notified
                $dateRange.trigger('change');
            }
        } else {
            console.log('📅 No date parameters found in URL');
        }
    }

    // Add loading styles
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
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        `)
        .appendTo('head');

    /**
     * Cancel any pending requests
     */
    function cancelPendingRequest() {
        if (currentAjaxRequest && currentAjaxRequest.readyState !== 4) {
           //console.log('⏹️ Cancelling pending request');
            currentAjaxRequest.abort();
            currentAjaxRequest = null;
        }

        if (debounceTimer) {
            clearTimeout(debounceTimer);
            debounceTimer = null;
        }
    }

    /**
     * Check if request parameters have changed
     */
    function hasParamsChanged(newParams) {
        if (!lastRequestParams) return true;
        return JSON.stringify(newParams) !== JSON.stringify(lastRequestParams);
    }

    /**
     * Optimized boat loading with debouncing and caching
     */
    window.loadBoatsAjax = function(paged, source) {
        paged = paged || 1;
        source = source || 'auto';

        // Build request parameters
        var urlParams = new URLSearchParams(window.location.search);
        var ajaxData = {
            action: 'bl_get_paginated_boats',
            paged: paged,
            charter_type: $('#charterType').val() || '',
            free_yacht: $('#dateRange').val() || '',
            model: '',
            company: '',
            location: $('#country').val() || '',
            cabin: $('#cabin').val() || '',
            person: $('#people').val() || '',
            year: $('#year').val() || '',
            category: urlParams.get('productName') || ''
        };

        // Check if this is a duplicate request
        if (source !== 'pagination' && !hasParamsChanged(ajaxData)) {
           //console.log('⏭️ Skipping duplicate request');
            return;
        }

        lastRequestParams = ajaxData;

        // For filter operations, debounce to avoid rapid requests
        if (source === 'filter') {
            cancelPendingRequest();

           //console.log(`⏳ Debouncing filter request (${DEBOUNCE_DELAY}ms)...`);

            debounceTimer = setTimeout(function() {
                executeBoatRequest(paged, source, urlParams, ajaxData);
            }, DEBOUNCE_DELAY);

            return;
        }

        // For pagination, execute immediately
        executeBoatRequest(paged, source, urlParams, ajaxData);
    };

    /**
     * Execute the actual AJAX request
     */
    function executeBoatRequest(paged, source, urlParams, ajaxData) {
        cancelPendingRequest();

        $('.boat-lists-loader').fadeIn();

        // Build AJAX URL with GET params
        var ajaxUrl = nausys_ajax_obj.ajax_url;
        var qs = [];

        ['country', 'productName', 'dateFrom', 'dateTo', 'charterType',
            'person', 'cabin', 'year', 'berths', 'wc', 'minLength', 'maxLength',
            'companyId', 'baseFromId', 'baseToId', 'sailingAreaId', 'modelId', 'flexibility'
        ].forEach(function(param) {
            var value = urlParams.get(param);
            if (value) {
                qs.push(param + '=' + encodeURIComponent(value));
            }
        });

        if (qs.length) ajaxUrl += '?' + qs.join('&');

       //console.log('📡 Executing boat request - Page:', paged, 'Source:', source);

        // Make AJAX request
        currentAjaxRequest = $.ajax({
            url: ajaxUrl,
            type: 'POST',
            timeout: REQUEST_TIMEOUT,
            data: ajaxData,
            cache: true, // Enable jQuery caching
            beforeSend: function() {
                if (source === 'filter') {
                    $('#submitFilter').prop('disabled', true).addClass('loading');
                }
            },
            success: function(res) {
               //console.log('✅ Request successful');
                requestRetryCount = 0;
                $('.boat-lists-loader').fadeOut();

                if (res.success && res.data) {
                    $('.boat-lists').html(res.data.boats_html);
                    $('.boat-listing-pagi').html(res.data.pagination_html);

                    var total = res.data.total_boats || 0;
                    var message = total > 0
                        ? '🔍 ' + total + ' boat' + (total > 1 ? 's' : '') + ' found'
                        : '🚫 No boats found';
                    $('#boat-count').text(message);

                    // Scroll to top of results smoothly
                    if (source === 'pagination') {
                        $('html, body').animate({
                            scrollTop: $('.boat-listing-area').offset().top - 100
                        }, 400);
                    }
                } else {
                    $('.boat-lists').html('<p>No boats found.</p>');
                    $('#boat-count').text('🚫 No boats found');
                }
            },
            error: function(xhr, status, error) {
                $('.boat-lists-loader').fadeOut();

                if (status === 'abort') {
                   //console.log('⏹️ Request cancelled');
                    return;
                }

                console.error('❌ Request failed:', status, error);

                // Handle timeout with retry
                if (status === 'timeout' && requestRetryCount < MAX_RETRIES) {
                    requestRetryCount++;
                   //console.log(`🔄 Retry ${requestRetryCount}/${MAX_RETRIES}...`);

                    setTimeout(function() {
                        executeBoatRequest(paged, source, urlParams, ajaxData);
                    }, 2000);
                    return;
                }

                // Show appropriate error message
                var errorMsg = '❌ Error loading boats. ';
                if (status === 'timeout') {
                    errorMsg = '⏰ Request timed out. The API might be slow. ';
                } else if (xhr.status === 0) {
                    errorMsg = '🔌 Connection issue. ';
                } else if (xhr.status >= 500) {
                    errorMsg = '🚨 Server error. ';
                }
                errorMsg += '<a href="#" onclick="location.reload(); return false;">Try again</a>';

                $('.boat-lists').html('<p>' + errorMsg + '</p>');
                $('#boat-count').text('❌ Error');

                requestRetryCount = 0;
            },
            complete: function() {
                if (source === 'filter') {
                    $('#submitFilter').prop('disabled', false).removeClass('loading');
                }
                $('.bl-page').removeClass('pagination-loading');
                currentAjaxRequest = null;
            }
        });
    }

    /**
     * Populate filters from URL
     */
    function populateFiltersFromUrl() {
        var urlParams = new URLSearchParams(window.location.search);

        if (urlParams.get('country')) {
            $('#country').val(urlParams.get('country')).trigger('change.select2');
        }

        if (urlParams.get('productName')) {
            $('#productName').val(urlParams.get('productName')).trigger('change.select2');
        }

        // Date handling is now done in initializeDateFromUrl() function

        // Optional parameters
        ['charterType', 'person', 'cabin', 'year'].forEach(function(param) {
            var value = urlParams.get(param);
            if (value) {
                var fieldMap = {
                    'person': 'people',
                    'charterType': 'charterType'
                };
                var fieldId = fieldMap[param] || param;
                $('#' + fieldId).val(value).trigger('change.select2');
            }
        });
    }

    /**
     * Filter button click handler
     */
    $('#submitFilter').on('click', function() {
       //console.log('🔍 Filter button clicked');

        var currentUrlParams = new URLSearchParams(window.location.search);
        var params = [];

        // Preserve existing parameters
        for (var [key, value] of currentUrlParams.entries()) {
            params.push(key + '=' + encodeURIComponent(value || ''));
        }

        // Update parameters helper
        function updateParam(paramName, newValue) {
            params = params.filter(function(p) {
                return !p.startsWith(paramName + '=');
            });
            params.push(paramName + '=' + encodeURIComponent(newValue || ''));
            return true;
        }

        // Update from form fields
        updateParam('country', $('#country').val());
        updateParam('productName', $('#productName').val());
        updateParam('charterType', $('#charterType').val());

        var person = $('#people').val();
        if (person && !isNaN(person) && parseInt(person) > 0) {
            updateParam('person', person);
        }

        var cabin = $('#cabin').val();
        if (cabin && !isNaN(cabin) && parseInt(cabin) > 0) {
            updateParam('cabin', cabin);
        }

        var year = $('#year').val();
        if (year && !isNaN(year) && parseInt(year) >= 1950) {
            updateParam('year', year);
        }

        // Handle date range
        var dateRange = $('#dateRange').val();
        if (dateRange) {
            var parts = dateRange.split(' to ');
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

        // Build URL and update browser
        var newUrl = '/boat/boat-filter' + (params.length ? '?' + params.join('&') : '');
        window.history.pushState({}, '', newUrl);

        // Load boats with debouncing
        loadBoatsAjax(1, 'filter');
    });

    /**
     * Pagination click handler
     */
    $(document).on('click', '.bl-page', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        var $clickedButton = $(this);

        $clickedButton.addClass('pagination-loading');

        loadBoatsAjax(page, 'pagination');
    });

    // Initialize
    populateFiltersFromUrl();

    // Delay date initialization to ensure Flatpickr is loaded
    setTimeout(function() {
        initializeDateFromUrl();
    }, 500);

    // Add visual feedback for date range picker
    $(document).on('change', '#dateRange', function() {
        var $this = $(this);
        var dateValue = $this.val();

        if (dateValue && dateValue.trim()) {
            console.log('📅 Date range changed to:', dateValue);

            // Enhanced visual styling for selected dates
            $this.css({
                'border-color': '#27ae60',
                'background-color': '#f0fff0',
                'box-shadow': '0 0 5px rgba(39, 174, 96, 0.3)',
                'transition': 'all 0.3s ease'
            });

            // Parse and set hidden fields for API compatibility
            var parts = [];
            if (dateValue.indexOf(' to ') !== -1) {
                parts = dateValue.split(' to ');
            } else if (dateValue.indexOf(' - ') !== -1) {
                parts = dateValue.split(' - ');
            }

            if (parts.length === 2) {
                // Convert DD.MM.YYYY to YYYY-MM-DDTHH:mm:ss
                function toApiFormat(dateStr) {
                    var d = dateStr.trim().split('.');
                    if (d.length === 3) {
                        return d[2] + '-' + d[1] + '-' + d[0] + 'T00:00:00';
                    }
                    return '';
                }

                var apiFromDate = toApiFormat(parts[0]);
                var apiToDate = toApiFormat(parts[1]);

                if (apiFromDate && apiToDate) {
                    $('#dateFrom').val(apiFromDate);
                    $('#dateTo').val(apiToDate);
                    console.log('📅 Hidden date fields updated:', apiFromDate, 'to', apiToDate);
                }
            }
        } else {
            console.log('📅 Date range cleared');

            // Reset to default styling
            $this.css({
                'border-color': '#ddd',
                'background-color': '',
                'box-shadow': '',
                'transition': 'all 0.3s ease'
            });

            // Clear hidden fields
            $('#dateFrom').val('');
            $('#dateTo').val('');
        }
    });

    // Auto-load if URL has parameters
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('country') || urlParams.get('productName') || urlParams.get('dateFrom')) {
        setTimeout(function() {
            loadBoatsAjax(1, 'auto');
        }, 300); // Reduced delay
    }

    // Clear cache button (add to admin area)
    if ($('#clear-boat-cache').length) {
        $('#clear-boat-cache').on('click', function() {
            $.post(nausys_ajax_obj.ajax_url, {
                action: 'bl_clear_cache'
            }, function(response) {
                alert('Cache cleared!');
                location.reload();
            });
        });
    }
});