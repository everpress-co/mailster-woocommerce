/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */
import { useEntityRecords } from '@wordpress/core-data';

/**
 * Internal dependencies
 */

export function useProducts(query) {
	return useEntityRecords('postType', 'product', query);
}
export function useCategories(query) {
	return useEntityRecords('taxonomy', 'product_cat', query);
}
export function mapResult(result) {
	if (!result) {
		return [];
	}

	return result
		.map((s, i) => {
			return {
				value: s.id,
				label: s.name || s.title.rendered,
			};
		})
		.sort((a, b) => a.value - b.value);
}
