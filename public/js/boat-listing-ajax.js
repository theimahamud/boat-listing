

(function($){


    function loadBoats(paged = 1) {

        $('.boat-lists-loader').fadeIn(); // show preloader
        $('#boat-count').text('🔄 Loading boats...');

        var charter_type = $('#boat_charter_type').val();
        var model = $('#boat_model').val();
        var company = $('#boat_company').val();
        var location = $('#boat_location').val();
        var cabin = $('#boat_cabin').val();
        var person = $('#boat_person').val();
        var year = $('#boat_year').val();
        var free_yacht = $('#search_free_yacht').val();
        var category = $('#boat_category').val();

        var ajaxData = {
            action: 'bl_get_paginated_boats',
            paged: paged,
            charter_type: charter_type,
            free_yacht: free_yacht,
            model: model,
            company: company,
            location: location,
            cabin: cabin,
            person: person,
            year: year
        };

        if (category && category !== '') {
            ajaxData.category = category;
        }

        $.ajax({
            url: nausys_ajax_obj.ajax_url,
            type: 'POST',
            timeout: 30000, // 30 seconds - boats should load fast now
            data: ajaxData,
            success: function(res) {
                $('.boat-lists-loader').fadeOut();

                // console.log('✅ Boats loaded:', res);

                if (res.success && res.data) {
                    // Render boats immediately
                    $('.boat-lists').html(res.data.boats_html);
                    $('.boat-listing-pagi').html(res.data.pagination_html);

                    const total = res.data.total_boats || 0;
                    const message = total > 0
                        ? `🔍 ${total} boat${total > 1 ? 's' : ''} found`
                        : `🚫 No boats found`;
                    $('#boat-count').text(message);

                // console.log('⏱️ Performance:', res.data.timing);

                    // 🔥 NEW: Check if prices already included (price-first approach)
                    if (res.data.has_prices) {
                        console.log('✅ Prices already included - no need to fetch separately');
                        return; // Exit - prices already displayed
                    }

                    // If user has selected a date range, fetch prices asynchronously
                    // Check BOTH backend flag AND actual field value (for page reload scenarios)
                    var dateFieldValue = $('#search_free_yacht').val();
                    var hasDateSelected = (res.data.has_date_filter && res.data.date_range) || dateFieldValue;

                    if (hasDateSelected) {
                        var dateRangeToUse = res.data.date_range || dateFieldValue;
                        console.log('💰 Date range detected:', dateRangeToUse);
                        loadBoatPrices(dateRangeToUse);
                    } else {
                        console.log('📅 No date range selected - skipping price fetch');
                    }
                } else {
                    $('.boat-lists').html('<p>No boats found.</p>');
                    $('#boat-count').text('🚫 No boats found');
                }
            },
            error: function(xhr, status, error) {
                $('.boat-lists-loader').fadeOut();

                if (status === 'timeout') {
                    $('.boat-lists').html('<p>⏱️ Request timed out. Please try again.</p>');
                    $('#boat-count').text('⏱️ Request timed out');
                } else {
                    $('.boat-lists').html('<p>❌ Error loading boats.</p>');
                    $('#boat-count').text('❌ Error loading boats');
                }
               // console.error('AJAX Error:', status, error);
            }
        });
    }

    // NEW: Load prices asynchronously after boats are displayed
    var priceLoadInProgress = false; // Prevent duplicate calls

    function loadBoatPrices(date_range) {
        // Prevent duplicate calls
        if (priceLoadInProgress) {
            //console.log('⚠️ Price loading already in progress, skipping...');
            return;
        }

        // Collect all boat IDs from displayed boats
        var boat_ids = [];
        $('.boat-list').each(function() {
            var boat_id = $(this).find('.boat-book-now').data('id');
            if (boat_id) {
                boat_ids.push(boat_id);
            }
        });

        if (boat_ids.length === 0) {
            //console.log('No boats to fetch prices for');
            return;
        }

        priceLoadInProgress = true;
       // console.log('💰 Fetching prices for', boat_ids.length, 'boats...');
       // console.log('📅 Date range:', date_range);

        // Show loading indicator on all price displays
        $('.price-info').html(
            '<div style="width:16px;height:16px;border:3px solid #e5e5e5;border-top:3px solid #777;border-radius:50%;animation:blspin 0.8s linear infinite;margin:0 auto;"></div>' +
            '<style>@keyframes blspin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}</style>'
        );

        var priceStartTime = Date.now();
        var priceInterval = setInterval(function() {
            var elapsed = Math.floor((Date.now() - priceStartTime) / 1000);
            if (elapsed > 10) {
                $('.price-info').html(
                    '<div style="width:16px;height:16px;border:3px solid #e5e5e5;border-top:3px solid #777;border-radius:50%;animation:blspin 0.8s linear infinite;margin:0 auto;"></div>' +
                    '<style>@keyframes blspin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}</style>'
                );
            }
        }, 5000);

        $.ajax({
            url: nausys_ajax_obj.ajax_url,
            type: 'POST',
            timeout: 300000, // 5 minutes for price API (it's very slow)
            data: {
                action: 'bl_get_boat_prices',
                date_range: date_range,
                boat_ids: boat_ids
            },
            success: function(res) {
                clearInterval(priceInterval);
                priceLoadInProgress = false;

                if (res.success && res.data && res.data.prices) {
                   // console.log('✅ Prices loaded:', res.data.boat_count, 'boats in', res.data.fetch_time, 's');

                    // Update each boat's price display
                    $('.boat-list').each(function() {
                        var $boat = $(this);
                        var boat_id = $boat.find('.boat-book-now').data('id');
                        var price_info = res.data.prices[boat_id];

                        if (price_info && price_info.min) {
                            // icon before price
                            var price_html = '<p><i class="ri-money-euro-circle-line" style="margin-right:4px;"></i>' +
                                'Price: ' + price_info.min + ' ' + (price_info.currency || 'EUR') +
                                '</p>';
                            $boat.find('.price-info').html(price_html);
                        } else {
                            var price_html = '<p style="color:#999;">' +
                                '<i class="ri-money-euro-circle-line" style="margin-right:4px;"></i>' +
                                'Price: N/A</p>';
                            $boat.find('.price-info').html(price_html);
                        }
                    });
                } else {
                    // Check if it's a database error
                    var errorMsg = 'Price: Contact for quote';
                    if (res && res.data && typeof res.data === 'string' && res.data.includes('WordPress database error')) {
                       // console.error('❌ Database error detected (MySQL server has gone away)');
                       // console.log('💡 Solution: Prices are being cached now, refresh page in 1 minute');
                        errorMsg = '💾 Caching prices... refresh in 1 min';
                    } else {
                       // console.warn('⚠️ Price API returned no data:', res);
                    }
                    $('.price-info').html('<p style="color:#999;">' + errorMsg + '</p>');
                }
            },
            error: function(xhr, status, error) {
                clearInterval(priceInterval);
                priceLoadInProgress = false;
               // console.error('❌ Price API Error:', status, error);
                $('.price-info').html('<p style="color:#999;">Price: N/A</p>');
            }
        });
    }


    // 🎯 Load boats on page load - with delay to ensure date picker is initialized
    // Increased delay to 800ms to ensure flatpickr completes initialization and sets default dates
    setTimeout(function() {
        console.log('🚀 Initial page load - checking date field...');
        var initialDateValue = $('#search_free_yacht').val();
        console.log('📅 Date field value:', initialDateValue || '(empty)');
        loadBoats(1); // Initial load with "Today → +7 days"
    }, 800);

    // Load first page on page load
    $(document).on('change', '#boat_charter_type, #search_free_yacht, #boat_model, #boat_company, #boat_location, #boat_cabin, #boat_year, #boat_category, #boat_person', function () {
        loadBoats(1); // Load page 1 on filter change
    });

    // Pagination click
    $(document).on('click', '.bl-page', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadBoats(page);
    });



    // Show modal
    $(document).on('click', '.boat-book-now', function (e) {

        e.preventDefault();

        var $button = $(this);
        var boatId = $button.data('id');
        var dateFrom = $button.data('date-from') || '';
        var dateTo = $button.data('date-to') || '';

        var $modal = $('.boatBookingModal');
        var preloader = nausys_ajax_obj.preloader;

        // Show modal and loader
        $modal.find('.modal-content').html(preloader);
        $modal.modal('show');

        // Load booking form via AJAX
        $.ajax({
        type: 'POST',
        url: nausys_ajax_obj.ajax_url,
        data: {
            action: 'bl_load_booking_modal',
            boat_id: boatId,
            date_from: dateFrom,
            date_to: dateTo
        },
        success: function (response) {
            $modal.find('.modal-content').html(response);

            // ✅ Initialize Select2 here
            $modal.find('.boat-listing-select2').select2({
                dropdownParent: $modal,
                dropdownCssClass: 'boat-listing-select2-dropdown',   // applies to dropdown
			    selectionCssClass: 'boat-listing-select2-single'  // applies to selected container
            });

            // ✅ Initialize Flatpickr date range picker
            $modal.find('.bl-date-range-picker').flatpickr({
                mode: "range",
                minDate: "today",
                dateFormat: "d-m-Y",
                allowInput: true,
                disable: [
                    function(date) {
                        // disable every multiple of 120
                        return !(date.getDate() % 120);
                    }
                ],
                onReady: function(selectedDates, dateStr, instance) {
                    instance.calendarContainer.classList.add('boat-listing-date-range');
                }
            });

            //$modal.find('.boat-listing-format-contact').intlInputPhone();

        },
        error: function () {
            $modal.find('.modal-content').html('<div class="modal-body text-danger">Something went wrong.</div>');
        }
        });
    });

    // Booking Data insert 
    $(document).on('submit', 'form#boat-booking-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var isValid = true;

        // Reset all previous errors
        $form.find('.is-invalid').removeClass('is-invalid');
        $form.find('.invalid-feedback').hide();

        // Validate required fields
        $form.find('input[required], textarea[required], select[required]').each(function () {
            var $field = $(this);
            var value = $field.val();

            if (
                value === null ||
                (Array.isArray(value) && value.length === 0) ||
                (typeof value === 'string' && !value.trim())
            ) {
                $field.addClass('is-invalid');
                $field.siblings('.invalid-feedback').show();
                isValid = false;
            }
        });

        if (!isValid) return; // Stop if invalid

        // Proceed with AJAX
        var formData = $form.serialize();
        var boatId = $form.find('input[name="boat_id"]').val();
        var $button = $form.find('button[type="submit"]');
        var originalText = $button.text();
        var button_spinner = nausys_ajax_obj.button_spinner;

        // Show spinner in button
        $button.html('Booking... ' + button_spinner);

        $.ajax({
            type: 'POST',
            url: nausys_ajax_obj.ajax_url,
            data: {
                action: 'bl_insert_book_reserve',
                boat_id: boatId,
                form_data: formData
            },
            success: function (res) {
                if (res.success) {
                    $('.message').notify('Message has been sent successfully', { className: 'success' });
                    $form.trigger('reset');
                    $form.find('select').val(null).trigger('change'); // Reset Select2
                    $button.html(originalText);
                } else {
                    $('.message').notify('Submission failed', { className: 'error' });
                }
            },
            error: function () {
                $('.message').notify('An error occurred.', { className: 'error' });
                $button.html(originalText);
            }
        });
    });

    // Real-time field validation: remove error when typing
    $(document).on('input change', '#boat-booking-form input[required], #boat-booking-form textarea[required], #boat-booking-form select[required]', function () {
        var $field = $(this);
        var value = $field.val();

        if (
            value !== null &&
            ((Array.isArray(value) && value.length > 0) ||
            (typeof value === 'string' && value.trim() !== ''))
        ) {
            $field.removeClass('is-invalid');
            $field.siblings('.invalid-feedback').hide();
        }
    });




})(jQuery);