(function($){

    $(document).ready(function(){
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
    })
})(jQuery);