(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 */

	// In your Javascript (external .js resource or <script> tag)
	$(document).ready(function() {

		// 🎯 Declare flatpickr instance variable at top scope for access by reset button
		var flatpickrInstance;

		// remove duplicate item from select2
		$('.boat-listing-select2').each(function() {
			let seen = {}; // reset for each select
			$(this).find('option').each(function() {
				let val = $(this).val();
				if (val === "") return;
				if (seen[val]) {
					$(this).remove();
				} else {
					seen[val] = true;
				}
			});
		});

		$('.boat-listing-select2').select2({
			dropdownCssClass: 'boat-listing-select2-dropdown',   // applies to dropdown
			selectionCssClass: 'boat-listing-select2-single'  // applies to selected container
		});

		$('.bl-slick-slider').slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			arrows: false,
			fade: true,
			asNavFor: '.bl-slick-slider-nav',
		});
		
		$('.bl-slick-slider-nav').slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			asNavFor: '.bl-slick-slider',
			dots: false,
			centerMode: true,
			focusOnSelect: true
		});

		// Fancybox init (optional config, can be omitted if defaults are okay)
		Fancybox.bind('[data-fancybox="gallery"]', {
			Thumbs: {
				autoStart: true
			}
		});

		$( ".boat-listing-tab" ).tabs();
		$('.open-tab-link').on('click', function(e){
			e.preventDefault();

			var targetId = $(this).attr('href'); // e.g., "#tab-price"
			var $tabs = $(".boat-listing-tab");

			// Find the index of the tab using the href
			var index = $tabs.find("ul li a[href='" + targetId + "']").parent().index();

			// Activate the tab
			$tabs.tabs("option", "active", index);

			// Optional: scroll to tabs
			$('html, body').animate({
				scrollTop: $tabs.offset().top
			}, 500);
		});

		// Data table
		$('.bl-data-table').DataTable({
			pageLength: 5, // number of rows per page
			lengthMenu: [20, 50, 100, 200], // options for rows per page
			searching: true, // enables search filter
			ordering: true, // allows sorting
		});

		// Date range picker - No auto-select for immediate price display
		flatpickrInstance = flatpickr(".bl-date-range-picker", {
			mode: "range",
			minDate: "today",
			dateFormat: "d.m.Y",
			defaultDate: null,

			disable: [
				function (date) {
					return !(date.getDate() % 360);
				}
			],

			onReady: function (selectedDates, dateStr, instance) {
				instance.calendarContainer.classList.add('boat-listing-date-range');

				// ✅ Force empty input on load
				instance.clear();
				instance.input.value = "";
			},

			onChange: function (selectedDates, dateStr, instance) {
				// Only when full range is selected
				if (selectedDates.length === 2) {
					var fromDate = instance.formatDate(selectedDates[0], "d.m.Y");
					var toDate = instance.formatDate(selectedDates[1], "d.m.Y");

					instance.input.value = fromDate + " to " + toDate;

					// Trigger AJAX filter only now
					$(instance.input).trigger("change");
				} else {
					instance.input.value = "";
				}
			}
		});

		// 🏠 Home page date picker - Auto-select tomorrow + 7 days
		flatpickr(".bl-home-date-range-picker", {
			mode: "range",
			minDate: "today",
			dateFormat: "d.m.Y",

			// Auto-select tomorrow + 7 days
			defaultDate: [
				new Date(new Date().setDate(new Date().getDate() + 1)), // Tomorrow
				new Date(new Date().setDate(new Date().getDate() + 8))  // Tomorrow + 7 days
			],

			disable: [
				function (date) {
					return !(date.getDate() % 360);
				}
			],

			onReady: function (selectedDates, dateStr, instance) {
				instance.calendarContainer.classList.add('boat-listing-date-range');

				// Format and display the auto-selected dates
				if (selectedDates.length === 2) {
					var fromDate = instance.formatDate(selectedDates[0], "d.m.Y");
					var toDate = instance.formatDate(selectedDates[1], "d.m.Y");
					instance.input.value = fromDate + " to " + toDate;
				}
			},

			onChange: function (selectedDates, dateStr, instance) {
				// Only when full range is selected
				if (selectedDates.length === 2) {
					var fromDate = instance.formatDate(selectedDates[0], "d.m.Y");
					var toDate = instance.formatDate(selectedDates[1], "d.m.Y");

					instance.input.value = fromDate + " to " + toDate;

					// Trigger AJAX filter
					$(instance.input).trigger("change");
				} else {
					instance.input.value = "";
				}
			}
		});

		// 🏠 Filter Desired Boat shortcode date picker - Auto-select tomorrow + 7 days (same as home page)
		flatpickr(".bl-filter-desired-date-range-picker", {
			mode: "range",
			minDate: "today",
			dateFormat: "d.m.Y",

			// Auto-select tomorrow + 7 days
			defaultDate: [
				new Date(new Date().setDate(new Date().getDate() + 1)), // Tomorrow
				new Date(new Date().setDate(new Date().getDate() + 8))  // Tomorrow + 7 days
			],

			disable: [
				function (date) {
					return !(date.getDate() % 360);
				}
			],

			onReady: function (selectedDates, dateStr, instance) {
				instance.calendarContainer.classList.add('boat-listing-date-range');

				// Format and display the auto-selected dates
				if (selectedDates.length === 2) {
					var fromDate = instance.formatDate(selectedDates[0], "d.m.Y");
					var toDate = instance.formatDate(selectedDates[1], "d.m.Y");
					instance.input.value = fromDate + " to " + toDate;
				}
			},

			onChange: function (selectedDates, dateStr, instance) {
				// Only when full range is selected
				if (selectedDates.length === 2) {
					var fromDate = instance.formatDate(selectedDates[0], "d.m.Y");
					var toDate = instance.formatDate(selectedDates[1], "d.m.Y");

					instance.input.value = fromDate + " to " + toDate;

					// Trigger change event for form handling
					$(instance.input).trigger("change");
				} else {
					instance.input.value = "";
				}
			}
		});

        $('#filter-desired-boat-form').on('submit', function (e) {
            e.preventDefault(); // stop form submission first

            var $dateInput = $('#dateRange');
            var dateValue  = $.trim($dateInput.val());

            // ================= REQUIRED CHECK =================
            if (!dateValue) {
                alert('📅 Please select your travel dates');
                $dateInput.focus();
                return false;
            }

            // ================= RANGE CHECK =================
            var parts = [];

            if (dateValue.indexOf(' to ') !== -1) {
                parts = dateValue.split(' to ');
            } else if (dateValue.indexOf(' - ') !== -1) {
                parts = dateValue.split(' - ');
            }

            if (parts.length !== 2 || !parts[0].trim() || !parts[1].trim()) {
                alert('📅 Please select a valid date range (start and end date)');
                $dateInput.focus();
                return false;
            }

            // ================= FORMAT FOR API =================
            function toApiFormat(dateStr) {
                var d = $.trim(dateStr).split('.');
                if (d.length !== 3) return '';
                return d[2] + '-' + d[1] + '-' + d[0] + 'T00:00:00';
            }

            var dateFrom = toApiFormat(parts[0]);
            var dateTo   = toApiFormat(parts[1]);

            if (!dateFrom || !dateTo) {
                alert('📅 Invalid date format');
                $dateInput.focus();
                return false;
            }

            $('#dateFrom').val(dateFrom);
            $('#dateTo').val(dateTo);

            // Disable visible date field so it doesn't get submitted
            $dateInput.prop('disabled', true);

            // ✅ finally submit the form
            this.submit();
        });

    });

})( jQuery );

// Open Hubspot chat box
(function waitForHubSpotAndButton() {
	// Try every 500ms to see if both HubSpot and the button are ready
	var interval = setInterval(function() {
		var hsWidgetReady = window.HubSpotConversations && window.HubSpotConversations.widget;
		var chatButton = document.getElementById('boat-listing-open-hubspot-chat-box');

		if (hsWidgetReady && chatButton) {
			chatButton.addEventListener('click', function() {
				window.HubSpotConversations.widget.open();
			});
			clearInterval(interval); // Stop checking
		}
	}, 500);
})();