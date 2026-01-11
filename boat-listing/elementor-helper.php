<?php
 
    function boat_listing_register_new_form_fields( $form_fields_registrar ) {

        require_once( __DIR__ . '/elementor/boat-listing-location.php' );
//        require_once( __DIR__ . '/elementor/boat-listing-model.php' );
//        require_once( __DIR__ . '/elementor/boat-listing-name.php' );

        $form_fields_registrar->register( new \boat_listing_location() );
//        $form_fields_registrar->register( new \boat_listing_models() );
//        $form_fields_registrar->register( new \boat_listing_name() );

    }

    add_action( 'elementor_pro/forms/fields/register', 'boat_listing_register_new_form_fields' );
