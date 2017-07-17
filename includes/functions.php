<?php

require_once OCRMLF_INCLUDES_DIR . '/leadform.php';
define('OCRM_EX1', '_1crm_ex1');
define('OCRM_EX2', '_1crm_ex2');

function ocrmlf_register_post_types() {
	OneCRMLeadForm::register_post_types();
}

function ocrm_lf_process_request_vars() {
	$request_uri = $_SERVER['REQUEST_URI'];
	$parsed = parse_url($request_uri);
	$source_number = $partner_number = null;
	if (!empty($parsed['query'])) {
		$parts = explode('&', $parsed['query']);
		foreach ($parts as $part) {
			if (preg_match('/^(\d+)|(\d+_)|(\d+_\d+)|(_\d+)$/', $part)) {
				$numbers = explode('_', $part);
				if (count($numbers) == 2)
					list($partner_number, $source_number) = $numbers;
				else {
					$partner_number = $numbers[0];
					$source_number = null;
				}
			}
		}
	}
	if (empty($partner_number))
		if (!empty($_COOKIE[OCRM_EX1]))
			$partner_number = $_COOKIE[OCRM_EX1];
	if (empty($source_number))
		if (!empty($_COOKIE[OCRM_EX2]))
			$source_number = $_COOKIE[OCRM_EX2];
	$partner_number = (int)$partner_number;
	$source_number = (int)$source_number;

	$params = array();
	if (!empty($partner_number)) {
		$params['_ex1'] = $partner_number;
		setcookie (OCRM_EX1, $partner_number, time() + 365 * 24 * 60 * 60, COOKIEPATH, COOKIE_DOMAIN); 
	}
	if (!empty($source_number)) {
		$params['_ex2'] = $source_number;
		setcookie (OCRM_EX2, $source_number, time() + 365 * 24 * 60 * 60, COOKIEPATH, COOKIE_DOMAIN); 
	}
	return $params;
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


