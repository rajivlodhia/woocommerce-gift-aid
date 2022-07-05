<div id="woocommerce_gift_aid">

	<?php

    // Get the title setting from WC.
	$title = WC_Admin_Settings::get_option('gift_aid__title');
	if ( !empty( $title ) ) {
		echo '<h2>' . __( $title ) . '</h2>';
	}

	// Get the explanation setting from WC.
    $explanation = WC_Admin_Settings::get_option('gift_aid__explanation');
	if ( !empty( $explanation ) ) {
		echo '<div>' . wpautop( __( $explanation ) ) . '</div>';
	}

	$label = WC_Admin_Settings::get_option('gift_aid__checkbox_text');
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
