<?php
/**
 * @package WooCommerce Gift Aid
 */

/*
Plugin Name: WooCommerce Gift Aid
Plugin URI: https://rajivlodhia.com/projects/google-static-maps-builder/
Description: A simple solution to getting Gift Aid permission from your customers on your WooCommerce store.
Version: 1.0.0
Author: Rajiv Lodhia
Author URI: https://rajivlodhia.com
License: GPLv2 or later
Text Domain: wcga-wc-gift-aid
*/

/**
 * Add Gift Aid option to the product backend.
 */
add_action( 'woocommerce_product_options_advanced', 'wcga_option_group' );
function wcga_option_group() {
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
add_action( 'woocommerce_process_product_meta', 'wcga_save_fields', 10, 2 );
function wcga_save_fields( $id, $post ){
	update_post_meta( $id, 'gift_aid_status', $_POST['gift_aid_status'] );
}

/**
 * Inject the Gift Aid field into the checkout page.
 */
add_action( 'woocommerce_review_order_before_payment', 'wcga_woocommerce_review_order_before_payment', 10, 0 );
function wcga_woocommerce_review_order_before_payment() {
	if ( _wcga_cart_has_gift_aid_product() ) {
		// Try to locate the template from the theme in case it wants to be overridden.
		$template = locate_template( [ 'template-gift-aid.php' ] );
		// If no template was found in the theme, use our own one.
		if ( empty( $template ) ) {
			$template = plugin_dir_path( __FILE__ ) . 'template-gift-aid.php';
		}

		load_template( $template );
	}
}

/**
 * Checks if the cart has a product with Gift Aid enabled.
 *
 * @return bool
 */
function _wcga_cart_has_gift_aid_product() {
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
add_action('woocommerce_checkout_update_order_meta', 'wcga_checkout_field_update_order_meta');
function wcga_checkout_field_update_order_meta($order_id) {
	if (!empty($_POST['woocommerce_gift_aid'])) {
		update_post_meta($order_id, '_gift_aid', sanitize_text_field($_POST['woocommerce_gift_aid']));
	}
}

/**
 * Show whether Gift Aid permission has been given on an order in the WC backend.
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'wcga_show_new_checkout_field_order', 10, 1 );
function wcga_show_new_checkout_field_order( $order ) {
	$order_id = $order->get_id();
	$permission_text = get_post_meta( $order_id, '_gift_aid', true ) == '1' ? 'Yes' : 'No';
	echo '<p><strong>Permission for Gift Aid:</strong> ' . $permission_text . '</p>';
}

/**
 * Create the section beneath the products tab
 **/
add_filter( 'woocommerce_get_sections_products', 'wcga_add_section' );
function wcga_add_section( $sections ) {
	$sections['gift_aid'] = __( 'Gift Aid', 'wcga-wc-gift-aid' );
	return $sections;
}

/**
 * Add settings to the specific section we created before
 */
add_filter( 'woocommerce_get_settings_products', 'wcga_all_settings', 10, 2 );
function wcga_all_settings( $settings, $current_section ) {
	/**
	 * Check the current section is what we want
	 **/
	if ( $current_section == 'gift_aid' ) {
		$settings_gift_aid = array();
		// Add Title to the Settings
		$settings_gift_aid[] = array(
			'name' => __( 'Gift Aid Settings', 'wcga-wc-gift-aid' ),
			'type' => 'title',
			'desc' => __( 'The following options are used to configure how the Gift Aid option works and appears on the checkout page to your customers.', 'wcga-wc-gift-aid' ),
			'id' => 'gift_aid',
		);
		// Add first checkbox option
		$settings_gift_aid[] = array(
			'name'     => __( 'Gift Aid Section Title', 'wcga-wc-gift-aid' ),
			'desc_tip' => __( 'E.g. "Reclaim Gift Aid"', 'wcga-wc-gift-aid' ),
			'id'       => 'gift_aid__title',
			'type'     => 'text',
			'desc'     => __( 'This is the title that appears at the top of your Gift Aid field on the checkout page.', 'wcga-wc-gift-aid' ),
			'default'  => __( 'Reclaim Gift Aid' ),
		);
		// Add second text field option
		$settings_gift_aid[] = array(
			'name'     => __( 'Gift Aid Explanation', 'wcga-wc-gift-aid' ),
			'desc_tip' => __( 'This is the body of text to explain Gift Aid to your customer.', 'wcga-wc-gift-aid' ),
			'id'       => 'gift_aid__explanation',
			'type'     => 'textarea',
			'desc'     => __( 'This is the body of text to explain Gift Aid to your customer.', 'wcga-wc-gift-aid' ),
			'default'  => __(''),
		);
		// Add checkbox text label option
		$settings_gift_aid[] = array(
			'name'     => __( 'Gift Aid Checkbox Text', 'wcga-wc-gift-aid' ),
			'desc_tip' => __( 'This text appears next to the checkbox', 'wcga-wc-gift-aid' ),
			'id'       => 'gift_aid__checkbox_text',
			'type'     => 'text',
			// 'desc'     => __( '', 'wcga-wc-gift-aid' ),
			'default'  => __( 'Yes, I would like to claim Gift Aid' ),
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
