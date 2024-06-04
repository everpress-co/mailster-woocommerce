/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

import { _x } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

export const ORDER_STATUS = [
	{
		name: _x('Pending payment', 'Order status', 'mailster-woocommerce'),
		id: 'pending',
	},
	{
		name: _x('Processing', 'Order status', 'mailster-woocommerce'),
		id: 'processing',
	},
	{
		name: _x('On hold', 'Order status', 'mailster-woocommerce'),
		id: 'on-hold',
	},
	{
		name: _x('Completed', 'Order status', 'mailster-woocommerce'),
		id: 'completed',
	},
	{
		name: _x('Cancelled', 'Order status', 'mailster-woocommerce'),
		id: 'cancelled',
	},
	{
		name: _x('Refunded', 'Order status', 'mailster-woocommerce'),
		id: 'refunded',
	},
	{
		name: _x('Failed', 'Order status', 'mailster-woocommerce'),
		id: 'failed',
	},
];
