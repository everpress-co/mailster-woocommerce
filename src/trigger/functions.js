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

/**
 * Loads WooCommerce products via the entity records store.
 *
 * @since 2.0.0
 *
 * @param {Object} query Query arguments for the product entity records.
 * @return {Object} Entity records result from useEntityRecords.
 */
export function useProducts(query) {
	return useEntityRecords('postType', 'product', query);
}
/**
 * Loads WooCommerce product categories via the entity records store.
 *
 * @since 2.0.0
 *
 * @param {Object} query Query arguments for the product_cat taxonomy.
 * @return {Object} Entity records result from useEntityRecords.
 */
export function useCategories(query) {
	return useEntityRecords('taxonomy', 'product_cat', query);
}
/**
 * Maps entity records to react-select option objects.
 *
 * @since 2.0.0
 *
 * @param {Array|null|undefined} result Entity records to map.
 * @return {Array<{value: number, label: string}>} Sorted select options.
 */
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
