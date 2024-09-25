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
export const fetchProducts = (productIds = []) => ({
	type: FETCH_PRODUCTS,
	productIds,
});

export const fetchProductsSuccess = (products) => ({
	type: FETCH_PRODUCTS_SUCCESS,
	products,
});

export const fetchProductsError = (error) => ({
	type: FETCH_PRODUCTS_ERROR,
	error,
});

// Controls
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
