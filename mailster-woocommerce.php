<?php

namespace EverPress;

/*
Plugin Name: Mailster for WooCommerce
Requires Plugins: mailster, woocommerce
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=WooCommerce
Description: add your WooCommerce customers to your Mailster lists
Version: 2.0.0
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-woocommerce
License: GPLv2 or later
*/


defined( 'ABSPATH' ) || exit;

if ( ! defined( 'MAILSTER_WOOCOMMERCE_FILE' ) ) {
	define( 'MAILSTER_WOOCOMMERCE_FILE', __FILE__ );
}

if ( ! class_exists( __NAMESPACE__ . '\MailsterWooCommerce' ) ) {
	include_once 'classes/woocommerce.class.php';
}
if ( ! class_exists( __NAMESPACE__ . '\MailsterWooCommerceAutomation' ) ) {
	include_once 'classes/automation.class.php';
}

\add_filter(
	'mailster_classes',
	function ( $classes ) {
		$classes['woocommerce']           = MailsterWooCommerce::get_instance();
		$classes['woocommerceAutomation'] = MailsterWooCommerceAutomation::get_instance();
		return $classes;
	}
);

\register_activation_hook(
	MAILSTER_WOOCOMMERCE_FILE,
	function () {
		MailsterWooCommerce::get_instance()->activate();
	}
);
\register_deactivation_hook(
	MAILSTER_WOOCOMMERCE_FILE,
	function () {
		MailsterWooCommerce::get_instance()->deactivate();
	}
);
