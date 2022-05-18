<div id="woocommerce_gift_aid">

	<?php

	$title = WC_Admin_Settings::get_option('gift_aid__title');
	if ( !empty( $title ) ) {
		echo '<h2>' . __( $title ) . '</h2>';
	}

	woocommerce_form_field( 'woocommerce_gift_aid', array(
		'type'        => 'checkbox',
		'class'       => array(
			'woocommerce-gift-aid-checkbox form-row-wide'
		),
		'label_class' => array(
			'woocommerce-gift-aid-checkbox-label'
		),
		'label'       => __( WC_Admin_Settings::get_option('gift_aid__checkbox_text') ),
	) );

	?>

</div>
