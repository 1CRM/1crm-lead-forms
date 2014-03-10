<?php

require_once OCRMLF_INCLUDES_DIR . '/functions.php';

add_action( 'init', 'ocrmlf_init' );
add_filter( 'map_meta_cap', 'ocrmlf_map_meta_cap', 10, 4 );

if (is_admin())
	include OCRMLF_ADMIN_DIR . '/admin.php';
else
	include OCRMLF_INCLUDES_DIR . '/controller.php';

function ocrmlf_init() {
	ocrmlf_register_post_types();
	do_action( 'ocrmlf_init' );
}

