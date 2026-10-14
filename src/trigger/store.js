/**
 * External dependencies
 */

/**
 * WordPress dependencies
 */

/**
 * Internal dependencies
 */

// create a store for the trigger block
// store.js
import { registerStore } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

// Action Types
const FETCH_PRODUCTS = 'FETCH_PRODUCTS';
const FETCH_PRODUCTS_SUCCESS = 'FETCH_PRODUCTS_SUCCESS';
const FETCH_PRODUCTS_ERROR = 'FETCH_PRODUCTS_ERROR';

// Actions
/**
 * Dispatches a products fetch request for the given IDs.
 *
 * @since 2.0.0
 *
 * @param {number[]} [productIds=[]] Product IDs to include.
 * @return {Object} Redux-style action.
 */
export const fetchProducts = (productIds = []) => ({
	type: FETCH_PRODUCTS,
	productIds,
});

/**
 * Dispatches a successful products fetch result.
 *
 * @since 2.0.0
 *
 * @param {Array} products Fetched products.
 * @return {Object} Redux-style action.
 */
export const fetchProductsSuccess = (products) => ({
	type: FETCH_PRODUCTS_SUCCESS,
	products,
});

/**
 * Dispatches a products fetch error.
 *
 * @since 2.0.0
 *
 * @param {*} error Fetch error.
 * @return {Object} Redux-style action.
 */
export const fetchProductsError = (error) => ({
	type: FETCH_PRODUCTS_ERROR,
	error,
});

// Controls
/**
 * Fetches products from the WooCommerce REST API.
 *
 * @since 2.0.0
 *
 * @param {Object} action Action containing productIds.
 * @return {Promise} API fetch promise.
 */
const fetchProductsControl = (action) => {
	const { productIds } = action;
	const queryArgs = productIds.length ? { exclude: productIds } : {};

	console.log(queryArgs);

	return apiFetch({
		path: '/wc/v3/products?a=b&include=' + productIds.join(','),
	});
};

// Reducer
const initialState = {
	products: [],
	isLoading: false,
	error: null,
};

/**
 * Reduces product store state.
 *
 * @since 2.0.0
 *
 * @param {Object} state  Current state.
 * @param {Object} action Dispatched action.
 * @return {Object} Next state.
 */
const reducer = (state = initialState, action) => {
	switch (action.type) {
		case FETCH_PRODUCTS:
			return {
				...state,
				isLoading: true,
				error: null,
			};
		case FETCH_PRODUCTS_SUCCESS:
			return {
				...state,
				isLoading: false,
				products: action.products,
			};
		case FETCH_PRODUCTS_ERROR:
			return {
				...state,
				isLoading: false,
				error: action.error,
			};
		default:
			return state;
	}
};

// Register Store with Controls and Resolvers
const storeConfig = {
	reducer,
	actions: {
		fetchProducts,
		fetchProductsSuccess,
		fetchProductsError,
	},
	selectors: {
		getProducts(state) {
			return state.products;
		},
		isLoading(state) {
			return state.isLoading;
		},
		getError(state) {
			return state.error;
		},
	},
	controls: {
		FETCH_PRODUCTS: fetchProductsControl,
	},
	resolvers: {
		*getProducts(productIds = []) {
			console.log(productIds);
			try {
				yield fetchProducts(productIds);
				const products = yield fetchProductsControl({ productIds });
				yield fetchProductsSuccess(products);
			} catch (error) {
				yield fetchProductsError(error);
			}
		},
	},
};

registerStore('mailster-woocommerce/store', storeConfig);
