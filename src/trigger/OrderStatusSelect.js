/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

import { __ } from '@wordpress/i18n';

import {
	BaseControl,
	TreeSelect,
	Tip,
	CheckboxControl,
	PanelRow,
	Panel,
	PanelBody,
} from '@wordpress/components';

/**
 * Internal dependencies
 */

import { ORDER_STATUS } from './constants';

export default function Selector(props) {
	const { attributes, setAttributes } = props;

	const { wooStatus, wooCreateUser = false } = attributes;

	return (
		<>
			<PanelRow>
				<BaseControl __nextHasNoMarginBottom>
					<TreeSelect
						__nextHasNoMarginBottom
						label={__('Order Status', 'mailster-woocommerce')}
						help={__(
							'Define the orderstatus for the trigger',
							'mailster-woocommerce'
						)}
						noOptionLabel={__('Any Order Status', 'mailster-woocommerce')}
						onChange={(newStatus) =>
							setAttributes({
								wooStatus: newStatus ? newStatus : undefined,
							})
						}
						selectedId={wooStatus}
						tree={ORDER_STATUS}
					/>
				</BaseControl>
			</PanelRow>

			{!wooStatus && (
				<PanelRow>
					<Tip>
						{__(
							'This workflow may run multiple times since order statuses can change repeatedly. To control the frequency, specify how often this trigger can be activated in the settings below.',
							'mailster-woocommerce'
						)}
					</Tip>
				</PanelRow>
			)}
			<PanelRow>
				<BaseControl
					__nextHasNoMarginBottom
					label={__('Subscriber', 'mailster-woocommerce')}
				>
					<CheckboxControl
						__nextHasNoMarginBottom
						label={__(
							'Create subscriber if not exists',
							'mailster-woocommerce'
						)}
						help={__(
							'This will create a subscriber if the user does not exist in the database.',
							'mailster-woocommerce'
						)}
						checked={wooCreateUser}
						onChange={(createUser) =>
							setAttributes({
								wooCreateUser: createUser ? true : undefined,
							})
						}
					/>
				</BaseControl>
			</PanelRow>
		</>
	);
}
