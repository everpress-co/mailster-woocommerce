/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */

import { ORDER_STATUS } from './constants';
import { useCategories } from './functions';

export default function Trigger(props) {
	const { attributes, setAttributes } = props;

	const { wooCategories, wooStatus, wooCreateUser } = attributes;

	const { hasResolved, isResolving, records, status, totalItems, totalPages } =
		useCategories({ include: wooCategories });

	const orderStatus = ORDER_STATUS.find((item) => item.id === wooStatus);

	if (isResolving || !hasResolved) {
		return null;
	}

	if (!wooCategories) {
		return (
			<p>
				{__(
					'Define Productcategories to trigger this workflow.',
					'mailster-woocommerce'
				)}
			</p>
		);
	}

	return (
		<>
			<p>
				{__(
					'When someone buys a product from one of the following categories:',
					'mailster-woocommerce'
				)}
			</p>
			<p>
				{records.map((record) => {
					return (
						<strong key={record.id} className="mailster-step-badge">
							{record.name || record.title.rendered}
						</strong>
					);
				})}
			</p>
			{orderStatus && (
				<>
					<p>
						{__(
							'and the status of the order is changed to',
							'mailster-woocommerce'
						)}
					</p>
					<p>
						<strong className="mailster-step-badge">{orderStatus.name}</strong>
					</p>
				</>
			)}
			{wooCreateUser && (
				<p>
					<i>{__('Create subscriber if not exists', 'mailster-woocommerce')}</i>
				</p>
			)}
		</>
	);
}
