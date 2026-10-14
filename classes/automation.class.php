<?php
/**
 * Mailster WooCommerce automation triggers.
 *
 * @package EverPress
 * @since 2.0.0
 */

namespace EverPress;

/**
 * WooCommerce automation triggers for Mailster workflows.
 *
 * @since 2.0.0
 */
class MailsterWooCommerceAutomation {

	/**
	 * Minimum Mailster version required for automation triggers.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	private $required_version = '4.1.4';

	/**
	 * Singleton instance.
	 *
	 * @since 2.0.0
	 *
	 * @var MailsterWooCommerceAutomation|null
	 */
	private static $instance = null;

	/**
	 * Hooks WooCommerce order changes into Mailster automation triggers.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {

		add_action( 'mailster_workflow_triggers', array( &$this, 'triggers' ) );
		add_action( 'enqueue_block_editor_assets', array( &$this, 'enqueue_block_editor_assets' ) );
		add_action( 'woocommerce_order_status_changed', array( &$this, 'order_status_changed' ), 10, 4 );
	}

	/**
	 * Returns the singleton instance.
	 *
	 * @since 2.0.0
	 *
	 * @return MailsterWooCommerceAutomation Instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new MailsterWooCommerceAutomation();
		}

		return self::$instance;
	}

	/**
	 * Resolves a Mailster subscriber from a WooCommerce order.
	 *
	 * @since 2.0.0
	 *
	 * @param int  $order_id Order ID.
	 * @param bool $create   Optional. Create a subscriber when none exists. Default false.
	 * @return object|null Subscriber object, or null when none found.
	 */
	private function get_subscriber_from_order( $order_id, $create = false ) {
		$order = wc_get_order( $order_id );

		$subscriber = null;

		// Get subscriber by email.
		if ( ! $subscriber ) {
			$subscriber = mailster( 'subscribers' )->get_by_mail( $order->get_billing_email() );
		}

		if ( ! $subscriber ) {
			// Get the WordPress user ID.
			$user_id    = $order->get_user_id();
			$subscriber = mailster( 'subscribers' )->get_by_wpid( $user_id );
		}

		// No subscriber found.
		if ( ! $subscriber ) {

			if ( $create ) {
				$subscriber_id = mailster( 'woocommerce' )->subscribe( $order_id );
				$subscriber    = mailster( 'subscribers' )->get( $subscriber_id );
			}
		}

		// No subscriber found.
		if ( ! $subscriber ) {

			return null;
		}
		return $subscriber;
	}

	/**
	 * Fires product and category automation workflows when an order status changes.
	 *
	 * @since 2.0.0
	 *
	 * @param int      $order_id               Order ID.
	 * @param string   $status_transition_from Previous order status.
	 * @param string   $status_transition_to   New order status.
	 * @param WC_Order $that                   Order object.
	 * @return void
	 */
	public function order_status_changed( $order_id, $status_transition_from, $status_transition_to, $that ) {

		unset( $that );

		$order = wc_get_order( $order_id );

		// Get products from the order.
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

					// Product is set but order does not contain the product.
					if ( isset( $option['wooProducts'] ) && ! array_intersect( $product_ids, $option['wooProducts'] ) ) {
						continue;
					}

					// Check status if set and order matches the defined one.
					if ( isset( $option['wooStatus'] ) && $status_transition_to !== $option['wooStatus'] ) {
						continue;
					}

					// Optionally create a subscriber.
					$create_user = isset( $option['wooCreateUser'] ) ? (bool) $option['wooCreateUser'] : false;

					// Resolve subscriber from the order.
					$subscriber = $this->get_subscriber_from_order( $order_id, $create_user );

					if ( ! $subscriber ) {
						continue;
					}

					// Trigger the workflow for the subscriber.
					mailster( 'triggers' )->trigger( $workflow, $option['trigger'], $subscriber->ID );
				}
			}
		}

		$workflows = mailster( 'triggers' )->get_workflows_by_trigger( 'woocommerce_category' );

		if ( $workflows ) {

			// Get categories from the order.
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

					// Categories are set but order does not contain the category.
					if ( isset( $option['wooCategories'] ) && ! array_intersect( $category_ids, $option['wooCategories'] ) ) {
						continue;
					}

					// Check status if set and order matches the defined one.
					if ( isset( $option['wooStatus'] ) && $status_transition_to !== $option['wooStatus'] ) {
						continue;
					}

					// Optionally create a subscriber.
					$create_user = isset( $option['wooCreateUser'] ) ? (bool) $option['wooCreateUser'] : false;

					// Resolve subscriber from the order.
					$subscriber = $this->get_subscriber_from_order( $order_id, $create_user );

					if ( ! $subscriber ) {
						continue;
					}

					// Trigger the workflow for the subscriber.
					mailster( 'triggers' )->trigger( $workflow, $option['trigger'], $subscriber->ID );
				}
			}
		}
	}


	/**
	 * Registers WooCommerce product and category triggers for Mailster workflows.
	 *
	 * @since 2.0.0
	 *
	 * @param array $triggers Existing workflow triggers.
	 * @return array Triggers including WooCommerce entries.
	 */
	public function triggers( $triggers ) {

		$supported = version_compare( MAILSTER_VERSION, $this->required_version, '>=' );

		$triggers[] = array(
			'id'       => 'woocommerce_product',
			'icon'     => 'cart',
			'label'    => esc_html__( 'Bought product', 'mailster-woocommerce' ),
			'info'     => esc_html__( 'When a users buys a product', 'mailster-woocommerce' ),
			'disabled' => ! $supported,
			/* translators: %s: required Mailster version number */
			'reason'   => sprintf( esc_html__( 'This feature requires Mailster %s', 'mailster-woocommerce' ), $this->required_version ),
		);
		$triggers[] = array(
			'id'       => 'woocommerce_category',
			'icon'     => 'cart',
			'label'    => esc_html__( 'Bought product in a category', 'mailster-woocommerce' ),
			'info'     => esc_html__( 'When a users buys a product from a specific category', 'mailster-woocommerce' ),
			'disabled' => ! $supported,
			/* translators: %s: required Mailster version number */
			'reason'   => sprintf( esc_html__( 'This feature requires Mailster %s', 'mailster-woocommerce' ), $this->required_version ),
		);

		return $triggers;
	}


	/**
	 * Enqueues block editor scripts for WooCommerce workflow triggers.
	 *
	 * @since 2.0.0
	 *
	 * @return void
	 */
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
