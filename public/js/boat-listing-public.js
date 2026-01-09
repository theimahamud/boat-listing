(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// In your Javascript (external .js resource or <script> tag)
	$(document).ready(function() {

		// 🎯 Declare flatpickr instance variable at top scope for access by reset button
		var flatpickrInstance;

		// remove duplicate item from select2
		//let seen = {};
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
	

		// Start display filter data
		const filterSelectors = [
			'#boat_charter_type',
			'#boat_model',
			'#boat_company',
			'#boat_location',
			'#boat_cabin',
			'#boat_person',
			'#boat_year',
			'#search_free_yacht',
			'#boat_category',
		];

		function getFilterLabel(id) {
			return $(`label[for="${id}"]`).text().trim();
		}

		function renderActiveFilters() {

			const $filteredLists = $('.filtered-lists');
			$filteredLists.empty();

			let hasFilters = false;

			filterSelectors.forEach(selector => {
				const $field = $(selector);
				const value = $field.val();
				const id = $field.attr('id');

				if (value && value !== "") {
					hasFilters = true;
					const label = getFilterLabel(id);

					let text = value; // default for inputs
					if ($field.is('select')) {
						text = $field.find('option:selected').text();
					}

					const filterItem = `
						<div class="active-filter" data-id="${id}">
							<strong>${label}: </strong> ${text}
							<span class="remove-filter" title="Remove">×</span>
						</div>
					`;
					$filteredLists.append(filterItem);
				}
			});

			$('.filter-summary-wrap').toggle(hasFilters);
		}


		// On change, re-render active filters
		$('.filter-bar select, .filter-bar input').on('change, input', function() {
			renderActiveFilters();
			// Optionally: Trigger Ajax filter call here
		});

		// Remove single filter
		$(document).on('click', '.remove-filter', function () {
			const fieldId = $(this).parent().data('id');
			const $field = $(`#${fieldId}`);

			// Clear the field value
			$field.val('');

			// Handle select dropdowns
			if ($field.is('select')) {
				$field.prop('selectedIndex', 0).trigger('change');
			}
			// Handle input fields (text, number, date, etc.)
			else if ($field.is('input')) {
				$field.trigger('input').trigger('change');
			}
			// Optional: for checkboxes or radio buttons
			else if ($field.is(':checkbox') || $field.is(':radio')) {
				$field.prop('checked', false).trigger('change');
			}

			// Re-render active filters UI
			renderActiveFilters();
		});

	// Reset all filters
	$('#reset-filters').on('click', function() {
		// 🎯 Better reset: handle Select2 properly without triggering change events yet
		filterSelectors.forEach(selector => {
			// Don't clear date picker - it will be reset to default below
			if (selector !== '#search_free_yacht') {
				var $el = $(selector);

				// For Select2 dropdowns, use .val(null) to clear properly
				if ($el.hasClass('boat-listing-select2') && $el.is('select')) {
					$el.val(null).trigger('change.select2'); // Update Select2 UI only
				} else if ($el.is('select')) {
					$el.prop('selectedIndex', 0);
				} else {
					$el.val('');
				}
			}
		});

		// 🎯 Reset date picker to default "Today → +7 days"
		if (typeof flatpickrInstance !== 'undefined' && flatpickrInstance) {
			const today = new Date();
			const nextWeek = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000);
			flatpickrInstance.setDate([today, nextWeek]);

			// Manually format and set the input value
			const fromDate = flatpickrInstance.formatDate(today, "d.m.Y");
			const toDate = flatpickrInstance.formatDate(nextWeek, "d.m.Y");
			$('#search_free_yacht').val(`${fromDate} to ${toDate}`);

			console.log('🔄 Reset filters - Date restored to:', `${fromDate} to ${toDate}`);
		}

		renderActiveFilters();

		// ✅ Trigger boat reload after Select2 has time to update its UI
		// Only trigger ONE filter to reload boats (avoid duplicate AJAX calls)
		setTimeout(function() {
			$('#boat_category').trigger('change');
		}, 100); // Increased to 100ms for smoother Select2 reset
	});

		// Initial load (in case filters are pre-filled)
		renderActiveFilters();

		// End display filtered data


		// Data table
		$('.bl-data-table').DataTable({
			pageLength: 5, // number of rows per page
			lengthMenu: [20, 50, 100, 200], // options for rows per page
			searching: true, // enables search filter
			ordering: true, // allows sorting
		});


	// Date range picker - Auto-select "Today to +7 days" for immediate price display
	flatpickrInstance = flatpickr(".bl-date-range-picker", {
		mode: "range",
		minDate: "today",
		dateFormat: "d.m.Y",
		// 🎯 Auto-select today to +7 days on page load
		defaultDate: [new Date(), new Date(Date.now() + 7 * 24 * 60 * 60 * 1000)],
		disable: [
			function(date) {
				// disable every multiple of 360
				return !(date.getDate() % 360);
			}
		],
		onReady: function(selectedDates, dateStr, instance) {
			instance.calendarContainer.classList.add('boat-listing-date-range');

			// 🎯 Auto-fill the input field with formatted date range on page load
			if (selectedDates.length === 2) {
				const fromDate = instance.formatDate(selectedDates[0], "d.m.Y");
				const toDate = instance.formatDate(selectedDates[1], "d.m.Y");
				const formattedRange = `${fromDate} to ${toDate}`;
				instance.input.value = formattedRange;

				// Force update via jQuery to ensure value is set
				$(instance.input).val(formattedRange);

				console.log('📅 Flatpickr initialized with dates:', formattedRange);
			}
		},
		onChange: function(selectedDates, dateStr, instance) {
			// Only set the input value when both from and to dates are selected
			if (selectedDates.length === 2) {
				const fromDate = instance.formatDate(selectedDates[0], "d.m.Y");
				const toDate = instance.formatDate(selectedDates[1], "d.m.Y");
				instance.input.value = `${fromDate} to ${toDate}`;

				// ✅ Trigger the AJAX filter only now
				$(instance.input).trigger('change');
			} else {
				// Clear input while user is still choosing range
				instance.input.value = "";
			}
		}
	});

	
	});


})( jQuery );


// Open Hubspot chat box 
(function waitForHubSpotAndButton() {
    // Try every 500ms to see if both HubSpot and the button are ready
    const interval = setInterval(() => {
        const hsWidgetReady = window.HubSpotConversations && window.HubSpotConversations.widget;
        const chatButton = document.getElementById('boat-listing-open-hubspot-chat-box');

        if (hsWidgetReady && chatButton) {
            chatButton.addEventListener('click', () => {
                window.HubSpotConversations.widget.open();
            });
            clearInterval(interval); // Stop checking
        }
    }, 500);
})();