<?php
/**
 * @package WooCommerce Gift Aid
 */

/*
Plugin Name: WooCommerce Gift Aid
Plugin URI: https://rajivlodhia.com/projects/woocommerce-giftaid-addon/
Description: A simple solution to getting Gift Aid permission from your customers on your WooCommerce store.
Version: 1.0.0
Author: Rajiv Lodhia
Author URI: https://rajivlodhia.com
License: GPLv2 or later
Text Domain: wcga-wc-gift-aid
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WoocommerceGiftAid {
	protected static $_instance = null;

	/**
	 * Plugin Instance
	 * Ensures only one instance of plugin is loaded or can be loaded.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
	    include_once plugin_dir_path( __FILE__ ) . '/class_wcga_requirements.php';

		add_action( 'plugins_loaded', array( $this, 'load_hooks' ), 9 );
	}

	public function load_hooks() {
	    $requirements = new WCGA_Requirements();

	    // Only load all our functionality hooks if the WP environment meets our requirements.
	    if ( $requirements->passed_requirements() ) {
            add_action( 'woocommerce_product_options_advanced', array( $this, 'wcga_option_group' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'wcga_save_fields' ), 10, 2 );
            add_action( 'woocommerce_review_order_before_payment', array( $this, 'wcga_woocommerce_review_order_before_payment' ), 10, 0 );
            add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'wcga_checkout_field_update_order_meta' ) );
            add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'wcga_show_new_checkout_field_order' ), 10, 1 );
            add_filter( 'woocommerce_get_sections_products', array( $this, 'wcga_add_section' ) );
            add_filter( 'woocommerce_get_settings_products', array( $this, 'wcga_all_settings' ), 10, 2 );
            add_action( 'woocommerce_admin_field_wysiwyg', array( $this, 'wcga_render_wysiwyg_field' ) );
            add_filter( 'woocommerce_admin_settings_sanitize_option_gift_aid__explanation', array( $this, 'unclean_giftaid_explanation_field' ), 10, 3 );

		    add_filter( 'manage_edit-shop_order_columns', array( $this, 'register_gift_aid_column' ), 10, 1 );
		    add_action( 'manage_shop_order_posts_custom_column', array( $this, 'display_gift_aid_column' ), 10, 1 );
		    add_action( 'restrict_manage_posts', array( $this, 'show_gift_aid_permission_filter_checkbox' ) );
		    add_filter( 'pre_get_posts', array( $this, 'filter_woocommerce_orders_in_the_table' ), 99, 1 );
		}
    }

	/**
     * Register our Gift Aid Permission column on the Orders table.
     *
	 * @param $columns
	 *
	 * @return array
	 */
	public function register_gift_aid_column( $columns ) {
		$new_columns = [];
		// Creating an updated list of columns to so we're able to add our new Gift Aid column right after the
        // Order Status column.
		foreach ( $columns as $column_id => $column_title ) {
			$new_columns[ $column_id ] = $column_title;
			if ( $column_id == 'order_status' ) {
				$new_columns['gift_aid_permission'] = __( 'Gift Aid Permission', 'wcga-wc-gift-aid' );
            }
		}
		return $new_columns;
	}

	/**
     * Displays the tick on the Order table rows where Gift Aid permission has been given for that order.
     *
	 * @param $column
	 */
	function display_gift_aid_column( $column ) {
		global $post;

		if ( 'gift_aid_permission' === $column ) {
			$gift_aid_status = get_post_meta( $post->ID, '_gift_aid', true );

			if ( $gift_aid_status !== false && strlen( $gift_aid_status ) > 0 ) {
				echo "✔️";
			}
		}
	}

	/**
	 * Renders the "Orders with Gift Aid Permission" filter checkbox.
	 */
	public function show_gift_aid_permission_filter_checkbox() {
		?>

        <label>
            Orders with Gift Aid Permission
            <input style="height: 16px;" type="checkbox" name="gift_aid_permission" id="gift_aid_permission" <?php echo isset( $_GET['gift_aid_permission'] ) ? 'checked' : ''; ?>>
        </label>

		<?php
    }

	/**
     * Filters the Orders table query for the Gift Aid Permission filter.
     *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function filter_woocommerce_orders_in_the_table( $query ) {
		if ( ! is_admin() ) {
			return $query;
		}

		global $pagenow;

		if ( 'edit.php' === $pagenow && 'shop_order' === $query->query['post_type'] ) {

			// We don't need to modify a query if a checkbox wasn't checked
			if ( ! isset( $_GET['gift_aid_permission'] ) ) {
				return $query;
			}

			$meta_query = array(
				array(
					'key' => '_gift_aid',
					'value' => 1,
					'compare' => '='
				)
			);

			$query->set( 'meta_query', $meta_query );
		}

		return $query;
	}

	/**
     * Add Gift Aid option to the product backend.
     */
    public function wcga_option_group() {
        echo '<div class="option_group option_group_gift_aid">';

        woocommerce_wp_checkbox( array(
            'id'      => 'gift_aid_status',
            'value'   => get_post_meta( get_the_ID(), 'gift_aid_status', true ),
            'label'   => __( 'Enable Gift Aid', 'wcga-wc-gift-aid' ),
            'desc_tip' => true,
            'description' => __( 'Should customers be given the option of Gift Aid for this product?', 'wcga-wc-gift-aid' ),
        ) );

        echo '</div>';
    }

    /**
     * Save WooCommerce backend options for Gift Aid.
     */
    public function wcga_save_fields( $id, $post ){
        update_post_meta( $id, 'gift_aid_status', $_POST['gift_aid_status'] );
    }

    /**
     * Inject the Gift Aid field into the checkout page.
     */
    public function wcga_woocommerce_review_order_before_payment() {
        if ( $this->_wcga_cart_has_gift_aid_product() ) {
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
    public function _wcga_cart_has_gift_aid_product() {
        $cart = WC()->cart;

        if ( !is_null( $cart ) ) {
            foreach ( $cart->get_cart_contents() as $item ) {
                $item['data'];
                if ( $item['data']->get_meta( 'gift_aid_status' ) === 'yes' ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Save the Gift Aid data to the product after checkout.
     */
    public function wcga_checkout_field_update_order_meta( $order_id ) {
        if ( !empty( $_POST[ 'woocommerce_gift_aid' ] ) ) {
            update_post_meta( $order_id, '_gift_aid', sanitize_text_field( $_POST[ 'woocommerce_gift_aid' ] ) );
        }
    }

    /**
     * Show whether Gift Aid permission has been given on an order in the WC backend.
     */
    public function wcga_show_new_checkout_field_order( $order ) {
        $order_id = $order->get_id();
        $permission_text = get_post_meta( $order_id, '_gift_aid', true ) == '1' ? __( 'Yes', 'wcga-wc-gift-aid' ) : __( 'No', 'wcga-wc-gift-aid' );
        echo __( '<p><strong>Permission for Gift Aid:</strong> ' . $permission_text . '</p>', 'wcga-wc-gift-aid' );
    }

    /**
     * Create the section beneath the products tab
     */
    public function wcga_add_section( $sections ) {
        $sections['gift_aid'] = __( 'Gift Aid', 'wcga-wc-gift-aid' );
        return $sections;
    }

    /**
     * Add settings to the specific section we created before
     */
    public function wcga_all_settings( $settings, $current_section ) {
        /**
         * Check the current section is what we want
         */
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
                'default'  => __( 'Reclaim Gift Aid', 'wcga-wc-gift-aid' ),
            );
            // Add second text field option
            $settings_gift_aid[] = array(
                'name'     => __( 'Gift Aid Explanation', 'wcga-wc-gift-aid' ),
                'desc_tip' => __( 'HTML can be used in this field', 'wcga-wc-gift-aid' ),
                'id'       => 'gift_aid__explanation',
                'type'     => 'wysiwyg',
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
                'default'  => __( 'Yes, I would like to claim Gift Aid', 'wcga-wc-gift-aid' ),
            );

            $settings_gift_aid[] = array( 'type' => 'sectionend', 'id' => 'gift_aid_sectionend' );
            return $settings_gift_aid;

            /**
             * If not, return the standard settings
             */
        } else {
            return $settings;
        }
    }

    /**
     * Render the TinyMCE WYSIWYG editor for the Gift Aid Explanation field.
     */
    public function wcga_render_wysiwyg_field( $value ) {
        $option_value = $value['value'];
        $field_description = WC_Admin_Settings::get_field_description( $value )

        ?>
        <tr valign="top">
            <th scope="row" class="titledesc">
                <label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?> <?php echo $field_description['tooltip_html']; // WPCS: XSS ok. ?></label>
            </th>
            <td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
                <?php
                    wp_editor( $option_value, 'gift_aid__explanation', array(
                        'editor_class' => 'gift_aid__explanation__tinymce',
                        'textarea_rows' => 10,
                        'teeny' => true,
                    ) );
                ?>
                <?php echo $field_description['description']; // WPCS: XSS ok. ?>
            </td>
        </tr>
        <?php
    }

    /**
     * Filter Gift Aid Explanation field on save to keep the HTML.
     * WooCommerce automatically cleans values so we need to re-set the value to the (sanitized) raw value.
     */
    public function unclean_giftaid_explanation_field( $value, $option, $raw_value ) {
        return wp_kses_post( $raw_value );
    }
}

function WCGA_SINGLETON() {
    return WoocommerceGiftAid::instance();
}
WCGA_SINGLETON();
