<?php

// Checking if Woocommerce Plugin is activated.
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'plugins_loaded', 'wide_payer_payment_gateway' );
}

/**
 * Function custom payment gateway wide payer
 */
function wide_payer_payment_gateway() {
	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       https://homescript1.gitlab.io/wide-payer/
	 * @since      1.0.0
	 *
	 * @package    Wide_Payer
	 * @subpackage Wide_Payer/payment-gateway
	 */

	/**
	 * The payment gateway main class.
	 *
	 * @package    Wide_Payer
	 * @subpackage Wide_Payer/payment-gateway
	 * @author     HomeScript <homescript1@gmail.com>
	 */
	class Wide_Payer_Payment_Gateway extends WC_Payment_Gateway {

		/**
		 * Wide Payer Payment Gateway constructor.
		 */
		public function __construct() {

			$this->id           = 'wide-payer';
			$this->icon         = plugins_url( '../images/icons.png', __FILE__ );
			$this->method_title = __( 'Wide Payer', 'wide-payer' );
			$this->title        = $this->method_title;
			$this->has_fields   = true;
			$this->init_form_fields();
			$this->init_settings();
			$this->enabled            = $this->get_option( 'enabled' );
			$this->description        = __( 'Payer vos commandes en ligne en utilisant Wide Payer MTN Momo ', 'wide-payer' );
			$this->method_description = $this->description;
			// This action hook saves the settings.
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			// Enqueue scripts for ajax.
			add_action( 'enqueue_scripts', array( $this, 'payment_scripts' ) );

			add_action( 'woocommerce_checkout_process', array( $this, 'validate_fields' ) );
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_post_meta' ) );
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'display_transaction_id' ), 10, 1 );
			$this->recipient_number = $this->get_option( 'recipient_number' );
			$this->recipient_name   = $this->get_option( 'recipient_name' );
            add_action( 'enqueue_scripts', array( $this, 'payments_scripts' ) );
		}

		/**
		 * Init woocommerce form fields.
		 *
		 * @return void
		 */
		public function init_form_fields() {

			$this->form_fields = array(
				'enabled'          => array(
					'title'   => __( 'Activer/Desactiver', 'wide-payer' ),
					'type'    => 'checkbox',
					'label'   => __( 'Activer/Desactiver Wide Payer', 'wide-payer' ),
					'default' => 'no',
				),
				'recipient_name'   => array(
					'title'       => __( 'Nom du receveur', 'wide-payer' ),
					'type'        => 'text',
					'description' => __( 'Nom du receveur du payement mobile. <u><strong>E.g :</strong></u> Dah Badou', 'wide-payer' ),
				),
				'recipient_number' => array(
					'title'       => __( 'Numero du receveur', 'wide-payer' ),
					'type'        => 'number',
					'description' => __( 'Numéro du receveur du payement mobile. <u><strong>E.g :</strong></u> 97979797', 'wide-payer' ),
				),
			);
		}

		/**
		 * Wide Payer front payment fields.
		 *
		 * @return void
		 */
		public function payment_fields() {

			if ( is_checkout() ) {
				$wide_payer       = new Wide_Payer_Payment_Gateway( false );
				$recipient_name   = $wide_payer->recipient_name;
				$recipient_number = $wide_payer->recipient_number;
				if ( ! empty( $recipient_name ) || ! empty( $recipient_number ) ) {
					wide_payer_payment_using_mobile_solutions( $recipient_name, $recipient_number );
					$args = array(
						'type'        => 'number',
						'label'       => __( 'ID de transaction MTN Mobile Money', 'wide-payer' ),
						'placeholder' => __( '456905010', 'wide-payer' ),
						'required'    => true,
						'class'       => array( 'form-row-wide' ),
					);
					echo '<br/><div class="mtn-momo-form">';
					woocommerce_form_field( '_mtn_momo', $args );
					echo '</div>';
					echo '<br/><div class="wide-payer-know-before">';
					echo '* L\'<strong>ID de transaction MTN Mobile Money</strong> est un <strong>champ requis</strong> et se trouve dans l\'accusé de reception d\'<strong>une transaction MTN Mobile Money</strong>.<br/>.';
					echo '</div>';
				} else {
					esc_html_e( "Veuillez-bien contactez l'administrateur de ce site , un problème est survenu." );
				}
			}
		}

		/**
		 * Process payments
		 *
		 * @param  mixed $order_id Woocommerce global variables.
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$order->update_status( 'on-hold', __( 'Votre payement est en cours , merci bien.', 'wide-payer' ) );
			WC()->cart->empty_cart();
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		/**
		 * Validate fields.
		 *
		 * @return void
		 */
		public function validate_fields() {
			global $order_id;
			if ( is_checkout() ) {
				$radio_choice = $_POST['_mtn_momo'];
				if ( empty( $radio_choice ) || preg_match( '/(\d){9}/', $radio_choice ) == false ) {
					wc_add_notice( 'Un ID de transaction ne peut être vide et doit contenir 9 chiffres.', 'error' );
				} elseif ( strlen( utf8_decode( $radio_choice ) ) != 9 ) {
					wc_add_notice( 'La valeur d\'un ID de transaction est de 9 chiffres , pas plus ni moins.', 'error' );
				}
			}
		}

		/**
		 *  Saving post meta.
		 *
		 * @param  mixed $order_id Woocommerce global variables.
		 * @return void
		 */
		public function save_post_meta( $order_id ) {

				$radio_choice = sanitize_text_field( wp_unslash( $_POST['_mtn_momo'] ) );
			if ( ! empty( $radio_choice ) || preg_match( '/(\d){9}/', $radio_choice ) == true ) {
				update_post_meta( $order_id, '_mtn_momo', sanitize_text_field( $radio_choice ) );
			}

		}

		/**
		 * Displays the transaction id in the details of the command.
		 *
		 * @param  mixed $order Woocommerce global variables.
		 * @return void
		 */
		public function display_transaction_id( $order ) {
			$order_data           = $order->get_data();
			$order_payment_method = $order_data['payment_method'];
			if ( 'wide-payer' == $order_payment_method ) {
				$transaction_id = get_post_meta( $order->get_id(), '_mtn_momo', true );
				echo '<div class="order_data_column"><h4>' . sprintf( esc_html( 'ID de transaction MTN Mobile Money : %s ', 'wide-payer' ), esc_html( $transaction_id ) ) . '</h4></div>';
			}
		}

				/**
				 * Enqueue payments scripts in the payments gateway.
				 */
		public function payments_scripts() {
			// Compatibilty for website using Elementor plugins
			// wp_enqueue_style( 'wide-payer-styles', plugin_dir_url( __FILE__ ) . 'css/wide-payer-styles.css', array(), '1.0', 'all' );
		}
	}

}

/**
 * Add Wide Payer class to all payment gateway methods.
 *
 * @param  mixed $methods Woocommerce Default Methods.
 * @return mixed
 */
function wide_payer_add_gateway_class( $methods ) {
	$methods[] = 'Wide_Payer_Payment_Gateway';
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'wide_payer_add_gateway_class' );

/**
 * Wide Paying function for manage mobile payments.
 *
 * @param  string  $recipient_name Recipient name.
 * @param  integer $recipient_number Recipient number.
 * @return mixed
 */
function wide_payer_payment_using_mobile_solutions( $recipient_name, $recipient_number ) {
	global $woocommerce;
	$cart_total_price = $woocommerce->cart->get_total();
	echo sprintf( __( 'Veuillez-bien envoyer le montant suivant : <strong>%1$s</strong> à Mr/Mme : <strong>%2$s</strong> au numéro de téléphone : <strong>%3$d</strong>  avec les frais de retraits.<br/>Veuillez bien mettre votre <strong>ID de transaction MTN Mobile Money</strong> dans le champ ci-dessous.', 'wide-payer' ), wp_kses_post( $cart_total_price ), esc_html( $recipient_name ), esc_html( $recipient_number ) );
}
