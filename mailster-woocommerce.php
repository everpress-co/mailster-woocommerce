<?php
/*
Plugin Name: Mailster for WooCommerce
Plugin URI: https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=WooCommerce
Description: add your WooCommerce customers to your Mailster lists
Version: 1.7
Author: EverPress
Author URI: https://mailster.co
Text Domain: mailster-woocommerce
License: GPLv2 or later
*/

define( 'MAILSTER_WOOCOMMERCE_VERSION', '1.7' );
define( 'MAILSTER_WOOCOMMERCE_REQUIRED_VERSION', '2.2.9' );
define( 'MAILSTER_WOOCOMMERCE_FILE', __FILE__ );

require_once dirname( __FILE__ ) . '/classes/woocommerce.class.php';
new MailsterWooCommerce();
