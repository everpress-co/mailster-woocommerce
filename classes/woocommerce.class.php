<?php

class MailsterWooCommerce {

	private $plugin_dir;
	private $plugin_url;

	public function __construct() {

		$this->plugin_path = plugin_dir_path( MAILSTER_WOOCOMMERCE_FILE );
		$this->plugin_url  = plugin_dir_url( MAILSTER_WOOCOMMERCE_FILE );

		register_activation_hook( MAILSTER_WOOCOMMERCE_FILE, array( &$this, 'activate' ) );
		register_deactivation_hook( MAILSTER_WOOCOMMERCE_FILE, array( &$this, 'deactivate' ) );

		load_plugin_textdomain( 'mailster-woocommerce' );

		add_action( 'plugins_loaded', array( &$this, 'init' ) );

	}


	public function init() {

		if ( ! function_exists( 'mailster' ) ) {

			add_action( 'admin_notices', array( &$this, 'notice' ) );
			return;

		}

		if ( is_admin() ) {

			add_filter( 'mailster_setting_sections', array( &$this, 'settings_tab' ) );
			add_action( 'mailster_section_tab_woocommerce', array( &$this, 'settings' ) );

			add_filter( 'woocommerce_settings_tabs_array', array( &$this, 'add_settings_tab' ), 100 );
			add_action( 'woocommerce_settings_tabs_mailster', array( &$this, 'mailster_settings_tab' ) );
			add_action( 'woocommerce_settings_mailster', array( &$this, 'woocommerce_settings_mailster' ) );

			add_action( 'add_meta_boxes_product', array( &$this, 'add_mailster_product_metabox' ) );

			add_action( 'save_post', array( &$this, 'save_product' ), 10, 2 );

		} else {

		}

		add_action( 'woocommerce_checkout_' . mailster_option( 'woocommerce_checkbox_pos', 'after_customer_details' ), array( &$this, 'checkbox' ) );
		add_action( 'woocommerce_' . mailster_option( 'woocommerce_checkbox_pos', 'after_customer_details' ), array( &$this, 'checkbox' ) );

		add_action( 'woocommerce_checkout_update_order_meta', array( &$this, 'on_checkout' ) );

		add_action( 'woocommerce_order_status_completed', array( &$this, 'on_completed' ) );

		add_action( 'woocommerce_email', array( &$this, 'remove_header_and_footer' ) );
		add_filter( 'mailster_wp_mail_template_file', array( &$this, 'set_template' ), 10, 3 );

	}

	public function add_settings_tab( $settings_tabs ) {

		$settings_tabs['mailster'] = 'Mailster';
		return $settings_tabs;
	}

	public function mailster_settings_tab() {
		woocommerce_admin_fields( $this->get_mailster_settings() );
	}

	public function woocommerce_settings_mailster() {

		echo '<h2>' . esc_html__( 'Settings can be found on the WooCommerce Settings page', 'mailster-woocommerce' ) . '</h2>';

		echo '<a class="button button-primary" href="edit.php?post_type=newsletter&page=mailster_settings#woocommerce">Settings Page</a>';

		echo '<style>input.woocommerce-save-button{display:none !important;}</style>';
	}

	public function get_mailster_settings() {

		return apply_filters(
			'woocommerce_mailster_settings',
			array(
				array(
					'title' => '',
					'type'  => 'title',
					'desc'  => '',
				),
				array( 'type' => 'sectionend' ),
			)
		);
	}

	public function mailster_lists() {
		$lists = mailster( 'lists' )->get();

		$mailster_lists[''] = __( 'Select a list', 'mailster-woocommerce' );

		foreach ( $lists as $list ) {
			$mailster_lists[ $list->ID ] = $list->name;
		}

		return $mailster_lists;
	}

	/**
	 *
	 *
	 * @param unknown $settings
	 * @return unknown
	 */
	public function settings_tab( $settings ) {

		$position = 5;
		$settings = array_slice( $settings, 0, $position, true ) +
			array( 'woocommerce' => 'WooCommerce' ) +
			array_slice( $settings, $position, null, true );

		return $settings;
	}


	public function add_mailster_product_metabox() {

		add_meta_box( 'mailster_product_metabox', 'Mailster', array( &$this, 'mailster_product_metabox' ), 'product', 'side', 'core' );
	}

	public function mailster_product_metabox( $post ) {

		wp_nonce_field( 'mailster_woocommerce_lists_nonce', 'mailster_woocommerce_lists_nonce' );
		$selected_lists = get_post_meta( $post->ID, '_mailster_lists', true );

		if ( ! is_array( $selected_lists ) ) {
			$selected_lists = mailster_option( 'woocommerce_lists' );
		}

		?>
		<?php mailster( 'lists' )->print_it( null, null, 'mailster_lists', false, $selected_lists ); ?>
		<p class="description">
			<?php _e( 'Customers who purchases this product will get added to these lists', 'mailster-woocommerce' ); ?>
		</p>

		<?php
	}

	public function save_product( $post_id, $post ) {

		if ( empty( $post ) || $post->post_type != 'product' ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST['mailster_woocommerce_lists_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['mailster_woocommerce_lists_nonce'], 'mailster_woocommerce_lists_nonce' ) ) {
			return;
		}

		$lists = isset( $_POST['mailster_lists'] ) ? (array) $_POST['mailster_lists'] : array();
		update_post_meta( $post_id, '_mailster_lists', $lists );

	}

	public function metabox() {

		include $this->plugin_path . '/views/metabox.php';

	}


	public function settings() {

		include $this->plugin_path . '/views/settings.php';

	}

	public function on_checkout( $order_id ) {

		if ( ( 'checkbox' == mailster_option( 'woocommerce_type' ) && isset( $_POST['mailster_signup'] ) )
			|| 'auto' == mailster_option( 'woocommerce_type' ) ) {

			if ( 'completed' == mailster_option( 'woocommerce_action' ) ) {
				add_post_meta( $order_id, 'mailster_signup', true, true );
			} elseif ( 'created' == mailster_option( 'woocommerce_action' ) ) {
				$this->subscribe( $order_id );
			}
		}

	}


	public function on_completed( $order_id ) {

		if ( get_post_meta( $order_id, 'mailster_signup', true ) ) {
			$this->subscribe( $order_id );
		}
	}

	public function subscribe( $order_id ) {

		$order = new WC_Order( $order_id );

		if ( ! $order ) {
			return;
		}

		if ( ! $order->get_billing_email() ) {
			return;
		}

		$default_lists = mailster_option( 'woocommerce_lists', array() );

		$email     = $order->get_billing_email();
		$firstname = $order->get_billing_first_name();
		$lastname  = $order->get_billing_last_name();

		foreach ( $order->get_items() as $item ) {

			$product_lists = get_post_meta( $item['product_id'], '_mailster_lists', true );

			if ( is_array( $product_lists ) ) {
				$default_lists = array_merge( $default_lists, $product_lists );
			}
		}

		if ( $subscriber = mailster( 'subscribers' )->get_by_mail( $email ) ) {
			$subscriber_id = $subscriber->ID;
		} else {

			$user_data = array(
				'firstname' => $firstname,
				'lastname'  => $lastname,
				'email'     => $email,
				'referer'   => sprintf( '<a href="' . admin_url( 'post.php?post=%d&action=edit' ) . '">%s #%d</a>', $order_id, __( 'Order', 'mailster-woocommerce' ), $order_id ),
				'status'    => mailster_options( 'woocommerce-double-opt-in' ) ? 0 : 1,
			);

			$synclist = mailster_option( 'sync' ) ? mailster_option( 'synclist', array() ) : array();
			foreach ( $synclist as $usermeta => $field ) {
				if ( method_exists( $order, 'get_' . $field ) && $value = call_user_func( array( $order, 'get_' . $field ) ) ) {
					$user_data[ $usermeta ] = $value;
				}
			}

			$subscriber_id = mailster( 'subscribers' )->add( $user_data );
		}

		if ( ! empty( $default_lists ) && ! is_wp_error( $subscriber_id ) ) {
			$lists   = array_unique( $default_lists );
			$success = mailster( 'subscribers' )->assign_lists( $subscriber_id, $lists, false );
		}
	}

	public function checkbox() {

		if ( 'auto' == mailster_option( 'woocommerce_type' ) ) {
			return;
		}

		if ( mailster_option( 'woocommerce-skip-user' ) && is_user_logged_in() && $subscriber = mailster( 'subscribers' )->get_by_wpid( get_current_user_id() ) ) {
			$output =  '<div class="mailster-signup"><input id="wc_mailster_signup" name="mailster_signup" type="hidden" value="1"></div>';
		} else {
			$output = '<div class="mailster-signup"><label for="wc_mailster_signup" class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox"><input id="wc_mailster_signup" name="mailster_signup" class="woocommerce-form__input-checkbox" type="checkbox" ' . checked( mailster_option( 'woocommerce_checkbox' ), true, false ) . '> <span>' . esc_html( mailster_option( 'woocommerce_label' ) ) . '</span></label></div>';
		}

		echo apply_filters( 'woocommerce_mailster_checkbox_output', $output );

	}

	public function set_template( $file, $caller, $current_filter ) {

		$default   = $file;
		$templates = mailster_option( 'woocommerce_templates', array() );
		add_filter( 'mailster_wp_mail_htmlify', '__return_false' );

		switch ( $current_filter ) {

			case 'woocommerce_order_status_pending_to_processing_notification':
			case 'woocommerce_order_status_pending_to_completed_notification':
			case 'woocommerce_order_status_pending_to_on-hold_notification':
			case 'woocommerce_order_status_failed_to_processing_notification':
			case 'woocommerce_order_status_failed_to_completed_notification':
			case 'woocommerce_order_status_failed_to_on-hold_notification':
				$file = isset( $templates['new_order'] ) ? $templates['new_order'] : $file;
				break;

			case 'woocommerce_order_status_pending_to_processing_notification':
			case 'woocommerce_order_status_pending_to_on-hold_notification':
				$file = isset( $templates['processing_order'] ) ? $templates['processing_order'] : $file;
				break;

			case 'woocommerce_order_status_completed_notification':
				$file = isset( $templates['completed_order'] ) ? $templates['completed_order'] : $file;
				break;

			case 'invoice':
				$file = isset( $templates['invoice'] ) ? $templates['invoice'] : $file;
				break;

			case 'woocommerce_created_customer_notification':
			case 'woocommerce_new_customer_note_notification':
				$file = isset( $templates['note'] ) ? $templates['note'] : $file;
				break;

			case 'woocommerce_reset_password_notification':
				$file = isset( $templates['reset_password'] ) ? $templates['reset_password'] : $file;
				break;

			case 'new_account':
				$file = isset( $templates['new_account'] ) ? $templates['new_account'] : $file;
				break;

			case 'woocommerce_process_shop_order_meta':
				if ( isset( $_POST['wc_order_action'] ) ) {
					switch ( $_POST['wc_order_action'] ) {
						case 'send_email_new_order':
							$file = isset( $templates['new_order'] ) ? $templates['new_order'] : $file;
							break;
						case 'send_email_cancelled_order':
							$file = isset( $templates['cancelled_order'] ) ? $templates['cancelled_order'] : $file;
							break;
						case 'send_email_customer_processing_order':
							$file = isset( $templates['processing_order'] ) ? $templates['processing_order'] : $file;
							break;
						case 'send_email_customer_completed_order':
							$file = isset( $templates['completed_order'] ) ? $templates['completed_order'] : $file;
							break;
						case 'send_email_customer_refunded_order':
							$file = isset( $templates['refunded_order'] ) ? $templates['refunded_order'] : $file;
							break;
						case 'send_email_customer_invoice':
							$file = isset( $templates['invoice'] ) ? $templates['invoice'] : $file;
							break;
					}
				}
				break;

		}

		return $file ? $file : $default;

	}

	public function remove_header_and_footer( $wooEmailObj ) {
		if ( mailster_option( 'system_mail' ) ) {
			remove_action( 'woocommerce_email_header', array( $wooEmailObj, 'email_header' ) );
			remove_action( 'woocommerce_email_footer', array( $wooEmailObj, 'email_footer' ) );
		}
	}

	public function activate() {
		if ( function_exists( 'mailster' ) ) {

			$defaults = array(
				'woocommerce_action'        => 'created',
				'woocommerce_type'          => 'checkbox',
				'woocommerce_checkbox'      => false,
				'woocommerce_checkbox_pos'  => 'after_customer_details',
				'woocommerce_label'         => __( 'Subscribe to our newsletter', 'mailster-woocommerce' ),
				'woocommerce_templates'     => array(
					'new_order'        => 0,
					'cancelled_order'  => 0,
					'refunded_order'   => 0,
					'processing_order' => 0,
					'completed_order'  => 0,
					'invoice'          => 0,
					'note'             => 0,
					'reset_password'   => 0,
					'new_account'      => 0,
				),
				'woocommerce-skip-user'     => true,
				'woocommerce-double-opt-in' => true,
			);

			$mailster_options = mailster_options();

			foreach ( $defaults as $key => $value ) {
				if ( ! isset( $mailster_options[ $key ] ) ) {
					mailster_update_option( $key, $value );
				}
			}
		}

	}


	public function deactivate() {
		if ( function_exists( 'mailster' ) ) {
		}
	}


	public function notice() {
		$msg = sprintf( __( 'You have to enable the %s to use Mailster for WooCommerce!', 'mailster-woocommerce' ), '<a href="https://rxa.li/mailster?utm_campaign=plugin&utm_medium=link&utm_source=Mailster+for+WooCommerce">Mailster Newsletter Plugin</a>' );
		?>
		<div class="error"><p><strong><?php echo $msg; ?></strong></p></div>
		<?php

	}

}
