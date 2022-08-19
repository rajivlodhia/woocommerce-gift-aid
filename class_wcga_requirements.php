<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WCGA_Requirements {

	/**
	 * Returns whether or not the WP environment passes our WooCommerce and PHP requirements.
	 *
	 * @return bool
	 */
	public function passed_requirements() {
		if ( $this->is_woocommerce_activated() === false ) {
			add_action( 'admin_notices', array ( $this, 'need_woocommerce' ) );
			return false;
		}

		if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
			add_action( 'admin_notices', array ( $this, 'required_php_version' ) );
			return false;
		}

		return true;
	}

	/**
	 * Check if woocommerce is activated
	 */
	public function is_woocommerce_activated() {
		$blog_plugins = get_option( 'active_plugins', array() );
		$site_plugins = is_multisite() ? (array) maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) ) : array();

		if ( in_array( 'woocommerce/woocommerce.php', $blog_plugins ) || isset( $site_plugins['woocommerce/woocommerce.php'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * WooCommerce not active notice.
	 */
	public function need_woocommerce() {
		/* translators: <a> tags */
		$error = sprintf( esc_html__( 'WooCommerce Gift Aid requires %1$sWooCommerce%2$s to be installed & activated!' , 'wcga-wc-gift-aid' ), '<a href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>' );

		$message = '<div class="error"><p>' . $error . '</p></div>';

		echo $message;
	}

	/**
	 * PHP version requirement notice
	 */
	public function required_php_version() {
		$error_message	= __( 'WooCommerce Gift Aid requires PHP 7.1 (7.4 or higher recommended).', 'wcga-wc-gift-aid' );
		$php_message	= __( 'We strongly recommend updating your PHP version.', 'wcga-wc-gift-aid' );

		$message = '<div class="error">';
		$message .= sprintf( '<p>%s</p>', $error_message );
		$message .= sprintf( '<p>'.$php_message.'</p>', '<a href="https://docs.wpovernight.com/general/how-to-update-your-php-version/" target="_blank">', '</a>' );
		$message .= '</div>';

		echo wp_kses_post( $message );
	}

}
