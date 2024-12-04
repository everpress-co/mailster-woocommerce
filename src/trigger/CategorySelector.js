/**
 * External dependencies
 */

import Select from 'react-select';

/**
 * WordPress dependencies
 */

import { __ } from '@wordpress/i18n';

import { useState, useEffect } from '@wordpress/element';

import { useDebounce } from '@wordpress/compose';

import { BaseControl } from '@wordpress/components';

/**
 * Internal dependencies
 */

import OrderStatusSelect from './OrderStatusSelect';
import { useCategories, mapResult } from './functions';

export default function Selector(props) {
	const { attributes, setAttributes } = props;

	const { wooCategories } = attributes;

	const [selectedTokens, setSelectedTokens] = useState([]);
	const [token, setToken] = useState();
	const [suggestions, setSuggestions] = useState([]);

	const title = __('Select Productcategory…', 'mailster-woocommerce');

	const help = __(
		'Select Produt Categories which trigger this workflow once bought.',
		'mailster-woocommerce'
	);

	const { hasResolved, isResolving, records, status, totalItems, totalPages } =
		useCategories({ include: wooCategories });

	const search = useCategories({ search: token });

	useEffect(() => {
		search.records && setSuggestions(mapResult(search.records));
	}, [search.records]);

	useEffect(() => {
		records && setSelectedTokens(mapResult(records));
	}, [records]);

	const searchTokensDebounce = useDebounce(setToken, 500);

	function setTokens(tokens) {
		const ids = tokens.map((token) => token.value);
		setAttributes({ wooCategories: ids.length ? ids : undefined });
		setSelectedTokens(tokens);
	}

	return (
		<>
			<BaseControl __nextHasNoMarginBottom label={help}>
				<Select
					options={suggestions}
					value={selectedTokens}
					placeholder={title}
					onInputChange={(tokens) => searchTokensDebounce(tokens)}
					onChange={(tokens) => setTokens(tokens)}
					isMulti
					isLoading={search.isResolving}
				/>
			</BaseControl>
			<OrderStatusSelect {...props} />
		</>
	);
}
