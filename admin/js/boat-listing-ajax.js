


(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
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


    /**
     * Sync Companies
     */
    $(document).on('click', '#bl-sync-boat-company', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-company').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_company'
            },
            success: function (res) {
                $('#bl-sync-spinner-company').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-company').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });


    /**
     * Sync free Yacht
     */
    $(document).on('click', '#bl-sync-freeyacht', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-freeyacht').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_freeyacht'
            },
            success: function (res) {
                $('#bl-sync-spinner-freeyacht').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-freeyacht').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });

    /**
     * Sync Boats
     */

    $(document).ready(function () {
       // boats insert in background process
        $(document).on('click', '#bl-sync-boats', function () {
            $('#bl-sync-spinner-boats').show();

            $.post(ajaxurl, { action: 'bl_start_boat_sync' }, function (res) {
                $('#bl-sync-spinner-boats').hide();

                if (res.success) {
                    alert('✅ Boat sync started. Cron will process in background.');
                } else {
                    alert('❌ Failed to start sync');
                }
            });
        });
    });

    /**
     * Sync Price Lists
     */
    $(document).on('click', '#bl-sync-price-list', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-price-list').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_price_lists'
            },
            success: function (res) {
                $('#bl-sync-spinner-price-list').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-price-list').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });

    /**
     * Sync Models
     */
    $(document).on('click', '#bl-sync-models', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-models').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_models'
            },
            success: function (res) {
                $('#bl-sync-spinner-models').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-models').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });


    /**
     * Sync Category
     */
    $(document).on('click', '#bl-sync-category', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-category').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_category'
            },
            success: function (res) {
                $('#bl-sync-spinner-category').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-category').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });

    /**
     * Sync Country
     */
    $(document).on('click', '#bl-sync-country', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-country').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_country'
            },
            success: function (res) {
                $('#bl-sync-spinner-country').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-country').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });
    /**
     * Sync Country state
     */
    $(document).on('click', '#bl-sync-countrystate', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-countrystate').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_country_state'
            },
            success: function (res) {
                $('#bl-sync-spinner-countrystate').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-countrystate').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });
    
    /**
     * Sync Country state
     */
    $(document).on('click', '#bl-sync-regions', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-regions').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_regions'
            },
            success: function (res) {
                $('#bl-sync-spinner-regions').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-regions').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });

    /**
     * Sync locations
     */
    $(document).on('click', '#bl-sync-locations', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-locations').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_locations'
            },
            success: function (res) {
                $('#bl-sync-spinner-locations').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-locations').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });

    /**
     * Sync charterbase
     */
    $(document).on('click', '#bl-sync-charterbase', function () {

        $('#bl-sync-status').text('');
        $('#bl-sync-spinner-charterbase').show();

        $.ajax({
            url: admin_ajax_obj.ajaxurl,
            method: 'POST',
            data: {
                action: 'bl_sync_boat_charterbase'
            },
            success: function (res) {
                $('#bl-sync-spinner-charterbase').hide();

                if (res.success) {
                    const results = res.data.results;
                    let statusMsg = '✅ Sync complete!\n\n';

                    for (const [key, result] of Object.entries(results)) {
                        const name = key.charAt(0).toUpperCase() + key.slice(1);
                        if (result.status === 'success') {
                            statusMsg += `✔️ ${name}: ${result.count} records synced.\n`;
                        } else {
                            statusMsg += `❌ ${name}: Failed to sync.\n`;
                        }
                    }

                    $('#bl-sync-status').text(statusMsg);
                } else {
                    $('#bl-sync-status').text('❌ Sync failed: ' + (res.data.message || 'Unknown error.'));
                }
            },
            error: function () {
                $('#bl-sync-spinner-charterbase').hide();
                $('#bl-sync-status').text('❌ AJAX error occurred.');
            }
        });
    });

})( jQuery );
