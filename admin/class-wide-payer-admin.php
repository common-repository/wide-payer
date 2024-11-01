<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://homescript1.gitlab.io/wide-payer/
 * @since      1.0.0
 *
 * @package    Wide_Payer
 * @subpackage Wide_Payer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wide_Payer
 * @subpackage Wide_Payer/admin
 * @author     HomeScript <homescript1@gmail.com>
 */
class Wide_Payer_Admin {


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
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wide_Payer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wide_Payer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wide-payer-admin.css', array(), $this->version, 'all' );
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
		 * defined in Wide_Payer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wide_Payer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wide-payer-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Function for check if woocommerce is activated
	 */
	public function check_if_woocommerce_is_active() {
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			?>
<div class=" notice notice-error">
<p>
			<?php
			esc_html_e( 'Wide Payer requiert l’activation de WooCommerce.', 'wide-payer' );
			?>
</p>
</div>
			<?php

		}
	}

	/**
	 * Add settings link
	 */
	public function link_to_settings( $links, $file ) {
		if ( 'wide-payer/wide-payer.php' === $file && current_user_can( 'manage_options' ) ) {
			$settings = array( 'settings' => '<a href="admin.php?page=wc-settings&tab=checkout&section=wide-payer">' . __( 'Réglages', 'wide-payer' ) . '</a>' );
			$links    = array_merge( $settings, $links );
		}
		return $links;
	}


}

