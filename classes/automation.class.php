<?php
namespace EverPress;

class MailsterWooCommerceAutomation {

	private $required_version = '4.1.4';
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

				foreach ( $options as $option ) {

					// product is set but order does not contain the product
					if ( isset( $option['wooProducts'] ) && ! array_intersect( $product_ids, $option['wooProducts'] ) ) {
						continue;
					}

					// check status if set and order is the defined one
					if ( isset( $option['wooStatus'] ) && $status_transition_to !== $option['wooStatus'] ) {
						continue;
					}

					// should we create a new user?
					$create_user = isset( $option['wooCreateUser'] ) ? (bool) $option['wooCreateUser'] : false;

					// get user from order and optionally create one
					$subscriber = $this->get_subscriber_from_order( $order_id, $create_user );

					if ( ! $subscriber ) {
						continue;
					}

					// trigger workflow with the found subscriber
					mailster( 'triggers' )->trigger( $workflow, $option['trigger'], $subscriber->ID );
				}
			}
		}

		$workflows = mailster( 'triggers' )->get_workflows_by_trigger( 'woocommerce_category' );

		if ( $workflows ) {

			// get cateogries from the order
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

				foreach ( $options as $option ) {

					// categories are set but order does not contain the category
					if ( isset( $option['wooCategories'] ) && ! array_intersect( $category_ids, $option['wooCategories'] ) ) {
						continue;
					}

					// check status if set and order is the defined one
					if ( isset( $option['wooStatus'] ) && $status_transition_to !== $option['wooStatus'] ) {
						continue;
					}

					// should we create a new user?
					$create_user = isset( $option['wooCreateUser'] ) ? (bool) $option['wooCreateUser'] : false;

					// get user from order and optionally create one
					$subscriber = $this->get_subscriber_from_order( $order_id, $create_user );

					if ( ! $subscriber ) {
						continue;
					}

					// trigger workflow with the found subscriber
					mailster( 'triggers' )->trigger( $workflow, $option['trigger'], $subscriber->ID );
				}
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
