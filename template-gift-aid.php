<div id="woocommerce-gift-aid">

	<?php

	// Get the title setting from WC.
	$title = WC_Admin_Settings::get_option( 'gift_aid__title' );
	$title = apply_filters( 'wcga_render_title_field', $title );
	if ( !empty( $title ) ) {
		echo '<h2 class="woocommerce-gift-aid--title">' . __( $title ) . '</h2>';
	}

	// Get the explanation setting from WC.
	$explanation = WC_Admin_Settings::get_option( 'gift_aid__explanation' );
	$explanation = apply_filters( 'wcga_render_explanation_field', $explanation );
	if ( !empty( $explanation ) ) {
		echo '<div class="woocommerce-gift-aid--explanation">' . wpautop( __( $explanation ) ) . '</div>';
	}

	$label = WC_Admin_Settings::get_option( 'gift_aid__checkbox_text' );
	woocommerce_form_field( 'woocommerce_gift_aid', array(
		'type'        => 'checkbox',
		'class'       => array(
			'woocommerce-gift-aid-checkbox form-row-wide'
		),
		'label_class' => array(
			'woocommerce-gift-aid-checkbox-label'
		),
		'label'       => __( !empty( $label ) ? $label : '' ),
	) );

	?>

</div>
