<?php
/**
 * @package Woocommerce Gift Aid
 */

/*
Plugin Name: Woocommerce Gift Aid
Plugin URI: https://rajivlodhia.com/projects/google-static-maps-builder/
Description: A simple solution to getting Gift Aid permission from your customers on your WooCommerce store.
Version: 1.0.0
Author: Rajiv Lodhia
Author URI: https://rajivlodhia.com
License: GPLv2 or later
Text Domain: rlwga-wc-gift-aid
*/

/**
 * Add Gift Aid option to the product backend.
 */
add_action( 'woocommerce_product_options_advanced', 'rlwga_option_group' );
function rlwga_option_group() {
	echo '<div class="option_group option_group_gift_aid">';

	woocommerce_wp_checkbox( array(
		'id'      => 'gift_aid_status',
		'value'   => get_post_meta( get_the_ID(), 'gift_aid_status', true ),
		'label'   => 'Enable Gift Aid',
		'desc_tip' => true,
		'description' => 'Should customers be given the option of Gift Aid for this product?',
	) );

	echo '</div>';
}

/**
 * Save WooCommerce backend options for Gift Aid.
 */
add_action( 'woocommerce_process_product_meta', 'rlwga_save_fields', 10, 2 );
function rlwga_save_fields( $id, $post ){
	update_post_meta( $id, 'gift_aid_status', $_POST['gift_aid_status'] );
}

/**
 * Inject the Gift Aid field into the checkout page.
 */
add_action( 'woocommerce_review_order_before_payment', 'rlwga_woocommerce_review_order_before_payment', 10, 0 );
function rlwga_woocommerce_review_order_before_payment() {
	if ( _rlwga_cart_has_gift_aid_product() ) {
		echo '<div id="woocommerce_gift_aid"><h2>' . __( 'Reclaim Gift Aid' ) . '</h2>';

		woocommerce_form_field( 'woocommerce_gift_aid', array(
			'type'        => 'checkbox',
			'class'       => array(
				'woocommerce-gift-aid-checkbox form-row-wide'
			),
			'label_class' => array(
				'woocommerce-gift-aid-checkbox-label'
			),
			'label'       => __( 'Boost your donation with Gift Aid' ),
		) );

		echo '</div>';
	}
}

/**
 * Checks if the cart has a product with Gift Aid enabled.
 *
 * @return bool
 */
function _rlwga_cart_has_gift_aid_product() {
	$cart = WC()->cart;

	if ( !is_null( $cart ) ) {
		foreach ( $cart->get_cart_contents() as $item ) {
			$item['data'];
			if ( $item['data']->get_meta('gift_aid_status') === 'yes' ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Save the Gift Aid data to the product after checkout.
 */
add_action('woocommerce_checkout_update_order_meta', 'rlwga_checkout_field_update_order_meta');
function rlwga_checkout_field_update_order_meta($order_id) {
	if (!empty($_POST['woocommerce_gift_aid'])) {
		update_post_meta($order_id, '_gift_aid', sanitize_text_field($_POST['woocommerce_gift_aid']));
	}
}

/**
 * Show whether Gift Aid permission has been given on an order in the WC backend.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'rlwga_show_new_checkout_field_order', 10, 1 );
function rlwga_show_new_checkout_field_order( $order ) {
	$order_id = $order->get_id();
	$permission_text = get_post_meta( $order_id, '_gift_aid', true ) == '1' ? 'Yes' : 'No';
	echo '<p><strong>Permission for Gift Aid:</strong> ' . $permission_text . '</p>';
}

/**
 * Create the section beneath the products tab
 **/
add_filter( 'woocommerce_get_sections_products', 'rlwga_add_section' );
function rlwga_add_section( $sections ) {
	$sections['gift_aid'] = __( 'Gift Aid', 'text-domain' );
	return $sections;
}

/**
 * Add settings to the specific section we created before
 */
add_filter( 'woocommerce_get_settings_products', 'rlwga_all_settings', 10, 2 );
function rlwga_all_settings( $settings, $current_section ) {
	/**
	 * Check the current section is what we want
	 **/
	if ( $current_section == 'gift_aid' ) {
		$settings_gift_aid = array();
		// Add Title to the Settings
		$settings_gift_aid[] = array(
			'name' => __( 'Gift Aid Settings', 'text-domain' ),
			'type' => 'title',
			'desc' => __( 'The following options are used to configure how the Gift Aid option works and appears on the checkout page to your customers.', 'text-domain' ),
			'id' => 'gift_aid',
		);
		// Add first checkbox option
		$settings_gift_aid[] = array(
			'name'     => __( 'Gift Aid Section Title', 'text-domain' ),
			'desc_tip' => __( 'E.g. "Reclaim Gift Aid"', 'text-domain' ),
			'id'       => 'gift_aid__title',
			'type'     => 'text',
			'desc'     => __( 'This is the title that appears at the top of your Gift Aid field on the checkout page.', 'text-domain' ),
			'default'  => __( 'Reclaim Gift Aid' ),
		);
		// Add second text field option
		$settings_gift_aid[] = array(
			'name'     => __( 'Gift Aid Explanation', 'text-domain' ),
			'desc_tip' => __( 'This is the body of text to explain Gift Aid to your customer.', 'text-domain' ),
			'id'       => 'gift_aid__explanation',
			'type'     => 'textarea',
			'desc'     => __( 'This is the body of text to explain Gift Aid to your customer.', 'text-domain' ),
			'default'  => __(''),
		);

		$settings_gift_aid[] = array( 'type' => 'sectionend', 'id' => 'gift_aid_sectionend' );
		return $settings_gift_aid;

		/**
		 * If not, return the standard settings
		 **/
	} else {
		return $settings;
	}
}
