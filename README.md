# Mailster for WooCommerce

Contributors: everpress, mailster, xaverb  
Tags: mailster, newsletter, ecommerce, leads, woocommerce  
Requires at least: 6.2  
Tested up to: 6.6  
Stable tag: 2.0.1  
Requires PHP: 7.4
License: GPLv2 or later

## Description

### Add your WooCommerce customers to your Mailster subscriber lists

> This Plugin requires [Mailster Newsletter Plugin for WordPress](https://mailster.co/?utm_campaign=wporg&utm_source=wordpress.org&utm_medium=readme&utm_term=WooCommerce)

Read more about the add on on our [knowledge base](https://kb.mailster.co/mailster-and-woocommerce/).

## Screenshots

### 1. Option Interface

![Option Interface](https://ps.w.org/mailster-woocommerce/assets/screenshot-1.png)

### 2. Product List Meta Box

![Product List Meta Box](https://ps.w.org/mailster-woocommerce/assets/screenshot-2.png)

### 3. Example Checkbox on Checkout

![Example Checkbox on Checkout](https://ps.w.org/mailster-woocommerce/assets/screenshot-3.png)

## Changelog

### 2.0.1

- fixed: PHP Warning-
- fixed: subscribers no longer unintentionally get unsubscribed on checkout submission

### 2.0.0

- new: Trigger for Mailster automations "Bought product"
- new: Trigger for Mailster automations "Bought in a product category"
- improved: better support for checkbox in new block based checkout
- some checkbox placement were deprecated as they no longer work on block based checkout
- new trigger require Mailster version 4.1.4 or above

### 1.7.1

- fixed: coupons are not displayed correctly in dynamic edit bar
- fixed: checks if current session has a customer before getting the subscriber by email

### 1.7

- added coupons to supported post types
- added coupons to tags list

### 1.6

- new: option to disable WooCommerece style in email

### 1.5

- fixed: use of deprecated method on order object
- updated markup of signup checkbox
- now requires WooCommerce 3.0 or above

### 1.4

- new option to skip checkbox for registered users

### 1.3

- fixed: admin url in referer

### 1.2

- updated file structure
- additional checkbox positions

### 1.1

- added: option to choose position of checkbox on the checkout page
- wrapped checkbox in label tag for better UX

### 1.0

- Initial Release
