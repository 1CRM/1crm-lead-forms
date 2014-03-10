<?php

require_once OCRMLF_INCLUDES_DIR . '/leadform.php';

function ocrmlf_register_post_types() {
	OneCRMLeadForm::register_post_types();
}

function ocrmlf_map_meta_cap( $caps, $cap, $user_id, $args ) {
	$meta_caps = array(
		'ocrmlf_edit' => 'publish_pages',
		'ocrmlf_read' => 'edit_posts',
	);

	$meta_caps = apply_filters( 'ocrmlf_map_meta_cap', $meta_caps );

	$caps = array_diff( $caps, array_keys( $meta_caps ) );

	if ( isset( $meta_caps[$cap] ) )
		$caps[] = $meta_caps[$cap];

	return $caps;
}

function ocrmlf_request_uri() {
	$uri = add_query_arg(array());
	return esc_url_raw( $uri );
}

function ocrmlf_lead_form($id) {
	$contact_form = new OneCRMLeadForm($id);

	if ( $contact_form->initial )
		return false;

	return $contact_form;
}


