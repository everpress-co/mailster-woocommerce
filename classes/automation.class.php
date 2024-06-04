<?php
namespace EverPress;

class MailsterWooCommerceAutomation {

	private $required_version = '4.1';
	private static $instance  = null;

	private function __construct() {

		add_action( 'mailster_workflow_triggers', array( &$this, 'triggers' ) );
		add_action( 'enqueue_block_editor_assets', array( &$this, 'enqueue_block_editor_assets' ) );
		add_action( 'woocommerce_order_status_changed', array( &$this, 'order_status_changed' ), 10, 4 );
	}

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new MailsterWooCommerceAutomation();
		}

		return self::$instance;
	}

	private function get_subscriber_from_order( $order_id, $create = false ) {
		$order = wc_get_order( $order_id );

		$subscriber = null;

		// get subscriber by email id
		if ( ! $subscriber ) {
			$subscriber = mailster( 'subscribers' )->get_by_mail( $order->get_billing_email() );
		}

		if ( ! $subscriber ) {
			// get the user id
			$user_id    = $order->get_user_id();
			$subscriber = mailster( 'subscribers' )->get_by_wpid( $user_id );
		}

		// no subscriber => no action
		if ( ! $subscriber ) {

			if ( $create ) {
				$subscriber_id = mailster( 'woocommerce' )->subscribe( $order_id );
				$subscriber    = mailster( 'subscribers' )->get( $subscriber_id );
			}
		}

		// no subscriber => no action
		if ( ! $subscriber ) {

			return null;
		}
		return $subscriber;
	}

	public function order_status_changed( $order_id, $status_transition_from, $status_transition_to, $that ) {

		$order = wc_get_order( $order_id );

		// fet products of the order
		$items = $order->get_items();

		$workflows = mailster( 'triggers' )->get_workflows_by_trigger( 'woocommerce_product' );

		if ( $workflows ) {

			$product_ids = array();
			foreach ( $items as $item ) {
				$product_ids[] = $item->get_product_id();
			}

			foreach ( $workflows as $workflow ) {

				$options = mailster( 'automations' )->get_trigger_option( $workflow, 'woocommerce_product' );

				// set but not included
				if ( isset( $options['wooProducts'] ) && ! array_intersect( $product_ids, $options['wooProducts'] ) ) {
					continue;
				}

				// check status if set
				if ( isset( $options['wooStatus'] ) && $status_transition_to !== $options['wooStatus'] ) {
					continue;
				}

				$create_user = isset( $options['wooCreateUser'] ) ? (bool) $options['wooCreateUser'] : false;

				$subscriber = $this->get_subscriber_from_order( $order_id, $create_user );

				if ( ! $subscriber ) {
					continue;
				}

				error_log( print_r( $subscriber, true ) );

				if ( isset( $options['wooStatus'] ) && $status_transition_to !== $options['wooStatus'] ) {
					continue;
				}

				mailster( 'triggers' )->trigger( $workflow, 'woocommerce_product', $subscriber->ID );

			}
		}

		return;

		$workflows = mailster( 'triggers' )->get_workflows_by_trigger( 'woocommerce_category' );

		if ( $workflows ) {

			// get cateogries
			$category_ids = array();
			foreach ( $items as $item ) {
				$product_id = $item->get_product_id();
				$terms      = get_the_terms( $product_id, 'product_cat' );
				if ( $terms ) {
					foreach ( $terms as $term ) {
						$category_ids[] = $term->term_id;
					}
				}
			}

			foreach ( $workflows as $workflow ) {

				$options = mailster( 'automations' )->get_trigger_option( $workflow, 'woocommerce_category' );

				// not set or not included
				if ( ! isset( $options['woo_categories'] ) || ! array_intersect( $category_ids, $options['woo_categories'] ) ) {
					continue;
				}

				// check status if set
				if ( isset( $options['woo_status'] ) && $status_transition_to !== $options['woo_status'] ) {
					continue;
				}

				mailster( 'triggers' )->trigger( $workflow, 'woocommerce_product', $subscriber_id );

			}
		}
	}


	public function triggers( $triggers ) {

		$supported = version_compare( MAILSTER_VERSION, $this->required_version, '>=' );

		$triggers[] = array(
			'id'       => 'woocommerce_product',
			'icon'     => 'cart',
			'label'    => esc_html__( 'Bought product', 'mailster-woocommerce' ),
			'info'     => esc_html__( 'When a users buys a product', 'mailster-woocommerce' ),
			'disabled' => ! $supported,
			'reason'   => sprintf( esc_html__( 'This feature requires Mailster %s', 'mailster-woocommerce' ), $this->required_version ),
		);
		$triggers[] = array(
			'id'       => 'woocommerce_category',
			'icon'     => 'cart',
			'label'    => esc_html__( 'Bought product in a category', 'mailster-woocommerce' ),
			'info'     => esc_html__( 'When a users buys a product from a specific category', 'mailster-woocommerce' ),
			'disabled' => ! $supported,
			'reason'   => sprintf( esc_html__( 'This feature requires Mailster %s', 'mailster-woocommerce' ), $this->required_version ),
		);

		return $triggers;
	}


	public function enqueue_block_editor_assets() {

		if ( 'mailster-workflow' !== get_post_type() ) {
			return;
		}

		$path = dirname( __DIR__ ) . '/build/trigger/';

		if ( ! file_exists( $path . 'index.asset.php' ) ) {
			return;
		}
		$dep = require $path . 'index.asset.php';

		wp_enqueue_script(
			'mailster-woocommerce-automation-trigger',
			plugins_url( 'build/trigger/index.js', __DIR__ ),
			$dep['dependencies'],
			$dep['version'],
			true
		);
	}
}
