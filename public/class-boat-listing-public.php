<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://designdevzone.com
 * @since      1.0.0
 *
 * @package    Boat_Listing
 * @subpackage Boat_Listing/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Boat_Listing
 * @subpackage Boat_Listing/public
 * @author     Design Develop Zone <kawsarr575@gmail.com>
 */
class Boat_Listing_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Boat_Listing_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Boat_Listing_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style('wp-admin');
		//wp_enqueue_style( $this->plugin_name . '_intl-tel-input', plugin_dir_url( __FILE__ ) . 'css/intl-tel-input.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_flatpickr', '//cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', array(), $this->version, 'all' );
		//wp_enqueue_style( $this->plugin_name . '_flatpickr_theme', '//cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_remixicon', '//cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_dataTable', '//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_select2', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_slick-slider', '//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_slick-slider-theme', '//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_fancybox', '//cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox/fancybox.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_jquery-tab', '//code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/boat-listing-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_responsive', plugin_dir_url( __FILE__ ) . 'css/boat-listing-responsive.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Boat_Listing_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Boat_Listing_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$helper = new Boat_Listing_Helper();

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( $this->plugin_name . '_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, false );
		//wp_enqueue_script( $this->plugin_name . '_intl-tel-input', plugin_dir_url( __FILE__ ) . 'js/intl-tel-input.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_dataTable', '//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_notify', '//cdn.jsdelivr.net/npm/notifyjs-browser@0.4.2/dist/notify.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_select2', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_slick-slider', '//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_fancybox', '//cdn.jsdelivr.net/npm/@fancyapps/ui/dist/fancybox/fancybox.umd.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/boat-listing-public.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'nausys-boat-ajax', plugin_dir_url( __FILE__ ) . 'js/boat-listing-ajax.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'boat-filter-page', plugin_dir_url( __FILE__ ) . 'js/boat-filter-page.js', array( 'jquery' ), $this->version, false );

		wp_localize_script('nausys-boat-ajax', 'nausys_ajax_obj', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce'    => wp_create_nonce('nausys_nonce'),
			'preloader' => $helper->bl_render_boat_overlay_preloader(),
			'button_spinner' => $helper->button_spinner(),
		]);

	}
}
