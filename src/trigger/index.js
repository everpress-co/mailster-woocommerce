/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { registerPlugin } from '@wordpress/plugins';

import { addFilter } from '@wordpress/hooks';

import { Fill } from '@wordpress/components';

import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */

import './editor.scss';
import ProductSelector from './ProductSelector';
import CategorySelector from './CategorySelector';
import ProductTrigger from './ProductTrigger';
import CategoryTrigger from './CategoryTrigger';

registerPlugin('mailster-woocommerce-trigger', {
	render: () => {
		// get all trigger blocks
		const triggerblocks = useSelect((select) =>
			select('core/block-editor').getBlocksByName('mailster-workflow/trigger')
		);

		return (
			<>
				<Fill name="mailster/trigger/selector">
					{(props) => {
						if (props.trigger === 'woocommerce_product') {
							return <ProductSelector {...props} />;
						}
						if (props.trigger === 'woocommerce_category') {
							return <CategorySelector {...props} />;
						}
					}}
				</Fill>
				{triggerblocks.map((clientId) => {
					const name = 'mailster/trigger/step/' + clientId;
					return (
						<Fill key={name} name={name}>
							{(props) => {
								if (props.trigger === 'woocommerce_product') {
									return <ProductTrigger {...props} />;
								}
								if (props.trigger === 'woocommerce_category') {
									return <CategoryTrigger {...props} />;
								}
							}}
						</Fill>
					);
				})}
			</>
		);
	},
});

// add custom attributes to trigger block
addFilter(
	'blocks.registerBlockType',
	'mailster-woocommerce/attributes',
	(settings) => {
		if (settings.name !== 'mailster-workflow/trigger') {
			return settings;
		}

		settings.attributes = Object.assign(settings.attributes, {
			wooProducts: {
				type: 'array',
			},
			wooCategories: {
				type: 'array',
			},
			wooStatus: {
				type: 'string',
			},
			wooCreateUser: {
				type: 'boolean',
			},
		});

		return settings;
	}
);
