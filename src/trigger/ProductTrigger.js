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
import { useProducts } from './functions';

export default function Trigger(props) {
	const { attributes, setAttributes } = props;

	const { wooProducts, wooStatus, wooCreateUser } = attributes;

	const { hasResolved, isResolving, records, status, totalItems, totalPages } =
		useProducts({ include: wooProducts });

	const orderStatus = ORDER_STATUS.find((item) => item.id === wooStatus);

	if (isResolving || !hasResolved) {
		return null;
	}

	return (
		<>
			<p>
				{wooProducts
					? __(
							'When someone buys one of the following products:',
							'mailster-woocommerce'
					  )
					: __(
							'When someone buys any product from your store',
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
