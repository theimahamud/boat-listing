<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://designdevzone.com
 * @since      1.0.0
 *
 * @package    Boat_Listing
 * @subpackage Boat_Listing/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Boat_Listing
 * @subpackage Boat_Listing/admin
 * @author     Design Develop Zone <kawsarr575@gmail.com>
 */
class Boat_Listing_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action('admin_menu', [$this, 'custom_admin_menu_page']);

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

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

		$current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';

		$allowed_pages = [
			'boat-listing',
			'boat-book-submissions',
		];

		// Bail if not on plugin’s pages
		if ( ! in_array( $current_page, $allowed_pages, true ) ) {
			return;
		}

		wp_enqueue_style( $this->plugin_name . '_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_remixicon', '//cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '_dataTable', '//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/boat-listing-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( $this->plugin_name . '_bootstrap', '//cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_dataTable', '//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/boat-listing-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name . '_ajax', plugin_dir_url( __FILE__ ) . 'js/boat-listing-ajax.js', array( 'jquery' ), time(), false );
		wp_localize_script( $this->plugin_name . '_ajax', 'admin_ajax_obj',
		array( 
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce'   => wp_create_nonce('bl_sync_nonce'),
		)
	);

	}


	function custom_admin_menu_page() {

		add_menu_page(
			'Boat Listing',    
			'Boat Listing',           
			'manage_options',       
			'boat-listing',  
			[$this, 'boat_listing_cb'], 
			'dashicons-admin-generic', 
			25                       
		);

		add_submenu_page(
			'boat-listing',                     // Parent slug (same as top-level)
			'Book Submissions',                 // Page title
			'Book Submissions',                 // Menu title
			'manage_options',                   // Capability
			'boat-book-submissions',            // Submenu slug
			[$this, 'boat_book_submissions_cb'] // Callback function
		);
	}

	

	function boat_listing_cb(){

		// Handle form submission
		if (isset($_POST['bl_api_setting_form']) && check_admin_referer('bl_api_setting_form_nonce')) {
			
			// API Setting
			update_option('bl_api_key', sanitize_text_field($_POST['bl_api_key']));

			// Mail Setting
			update_option('bl_from_mail', sanitize_text_field($_POST['bl_from_mail']));
			update_option('bl_from_name', sanitize_text_field($_POST['bl_from_name']));
			update_option('bl_to_mail', sanitize_text_field($_POST['bl_to_mail']));

			echo '<div class="notice notice-success is-dismissible"><p>Settings saved!</p></div>';
		}

		require_once plugin_dir_path( __FILE__ ) . 'partials/pages/boat-listing.php';

	}

	function boat_book_submissions_cb(){

		require_once plugin_dir_path( __FILE__ ) . 'partials/pages/boat-book-submissions.php';

	}


}
