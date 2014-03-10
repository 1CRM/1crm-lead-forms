<?php

add_action( 'admin_menu', 'ocrmlf_admin_menu', 9 );
require_once OCRMLF_ADMIN_DIR . '/admin-functions.php';

function ocrmlf_admin_menu() {
	$icon_url = '';

	add_object_page(
		__('1CRM Lead Form', OCRMLF_TEXTDOMAIN),
		__('Lead Form', OCRMLF_TEXTDOMAIN),
		'ocrmlf_read',
		'ocrmlf',
		'ocrmlf_admin_page',
		$icon_url
	);

	$edit = add_submenu_page(
		'ocrmlf',
		__('Edit Lead Form', OCRMLF_TEXTDOMAIN),
		__('Lead Forms', OCRMLF_TEXTDOMAIN),
		'ocrmlf_read', 'ocrmlf',
		'ocrmlf_admin_page' );

	add_action( 'load-' . $edit, 'ocrmlf_admin_load' );

	$addnew = add_submenu_page(
		'ocrmlf',
		__('Add New Lead Form', OCRMLF_TEXTDOMAIN),
		__('Add New', OCRMLF_TEXTDOMAIN),
		'ocrmlf_edit',
		'ocrmlf-new',
		'ocrmlf_admin_edit' );

	add_action( 'load-' . $addnew, 'ocrmlf_admin_load' );
}

function ocrmlf_admin_page() {
	if ( $post = ocrmlf_get_current_form() ) {
		$post_id = $post->initial ? -1 : $post->id;
		include OCRMLF_ADMIN_DIR . '/tpl-edit-form.php';
		return;
	}
	require_once OCRMLF_ADMIN_DIR . '/list-table.php';
	include OCRMLF_ADMIN_DIR . '/tpl-list-forms.php';
}

function ocrmlf_admin_edit() {
	if ( $post = ocrmlf_get_current_form() ) {
		$post_id = -1;

		include OCRMLF_ADMIN_DIR . '/tpl-edit-form.php';
		return;
	}
}

function ocrmlf_admin_load() {
	global $plugin_page;

	$action = ocrmlf_current_action();

	if ( 'save' == $action ) {
		$id = $_POST['post_ID'];
		check_admin_referer( 'ocrmlf-save-contact-form_' . $id );

		if (!current_user_can( 'ocrmlf_edit', $id))
			wp_die(__('You are not allowed to edit this item.', OCRMLF_TEXTDOMAIN));

		if ( !($contact_form = ocrmlf_lead_form($id)) ) {
			$contact_form = new OneCRMLeadForm();
			$contact_form->initial = true;
		}

		$contact_form->title = trim( $_POST['ocrmlf-title'] );

		$form = trim($_POST['ocrmlf-form']);
		$fields = $_POST['ocrmlf-fields'];
		$mail_from = $_POST['ocrmlf-from'];
		$mail_to = $_POST['ocrmlf-to'];
		$mail_subject = $_POST['ocrmlf-subject'];
		$mail_body = $_POST['ocrmlf-body'];
		$url = $_POST['ocrmlf-url'];
		$success_url = $_POST['ocrmlf-success-url'];
		$method = $_POST['ocrmlf-method'];
		$messages = $_POST['ocrmlf-msg'];

		$props = apply_filters(
			'ocrmlf_admin_posted_properties',
			compact('form', 'fields', 'mail_from', 'mail_to', 'mail_subject', 'mail_body', 'url', 'method', 'messages', 'success_url')
		);

		foreach ( (array) $props as $key => $prop )
			$contact_form->{$key} = $prop;

		$query = array();
		$query['message'] = ($contact_form->initial) ? 'created' : 'saved';

		$contact_form->save();

		$query['post'] = $contact_form->id;

		$redirect_to = add_query_arg( $query, menu_page_url( 'ocrmlf', false ) );

		wp_safe_redirect( $redirect_to );
		exit();
	}

	if ( 'copy' == $action ) {
		$id = empty( $_POST['post_ID'] ) ? absint($_REQUEST['post']) : absint($_POST['post_ID']);

		check_admin_referer( 'ocrmlf-copy-contact-form_' . $id );

		if (!current_user_can( 'ocrmlf_edit', $id))
			wp_die(__('You are not allowed to edit this item.', OCRMLF_TEXTDOMAIN));

		$query = array();

		if ( $contact_form = ocrmlf_lead_form($id)) {
			$new_contact_form = $contact_form->copy();
			$new_contact_form->save();

			$query['post'] = $new_contact_form->id;
			$query['message'] = 'created';
		}

		$redirect_to = add_query_arg($query, menu_page_url('ocrmlf', false));

		wp_safe_redirect($redirect_to);
		exit();
	}

	if ( 'delete' == $action ) {
		if ( ! empty( $_POST['post_ID'] ) )
			check_admin_referer( 'ocrmlf-delete-contact-form_' . $_POST['post_ID'] );
		elseif ( ! is_array( $_REQUEST['post'] ) )
			check_admin_referer( 'ocrmlf-delete-contact-form_' . $_REQUEST['post'] );
		else
			check_admin_referer( 'bulk-posts' );

		$posts = empty($_POST['post_ID']) ? (array)$_REQUEST['post'] : (array) $_POST['post_ID'];

		$deleted = 0;

		foreach ( $posts as $post ) {
			$post = new OneCRMLeadForm($post);

			if ( empty( $post ) )
				continue;

			if ( ! current_user_can( 'ocrmlf_edit', $post->id ) )
				wp_die(__('You are not allowed to delete this item.', OCRMLF_TEXTDOMAIN));

			if (!$post->delete())
				wp_die(__( 'Error in deleting.', OCRMLF_TEXTDOMAIN));

			$deleted += 1;
		}

		$query = array();

		if (!empty($deleted))
			$query['message'] = 'deleted';

		$redirect_to = add_query_arg($query, menu_page_url('ocrmlf', false));

		wp_safe_redirect( $redirect_to );
		exit();
	}

	$_GET['post'] = isset($_GET['post']) ? $_GET['post'] : '';

	$post = null;

	if ('ocrmlf-new' == $plugin_page) {
		$post = new OneCRMLeadForm;
		$post->initial = true;
	} elseif (!empty($_GET['post'])) {
		$post = ocrmlf_lead_form($_GET['post']);
	}

	if ( $post && current_user_can( 'ocrmlf_edit', $post->id ) ) {
		ocrmlf_add_meta_boxes($post->id);
		include OCRMLF_ADMIN_DIR . '/help.php';
	} else {
		$current_screen = get_current_screen();

		require_once OCRMLF_ADMIN_DIR . '/list-table.php';

		add_filter( 'manage_' . $current_screen->id . '_columns',
			array( 'OneCRMLeads_List_Table', 'define_columns' ) );

		add_screen_option(
			'per_page',
			array(
				'label' => __('Lead Forms', OCRMLF_TEXTDOMAIN),
				'default' => 20,
				'option' => 'ocrmlf_per_page'
			)
		);
	}

	if ( $post ) {
		OneCRMLeadForm::set_current( $post );
	}
}

add_action( 'admin_enqueue_scripts', 'ocrmlf_admin_enqueue_scripts' );

function ocrmlf_admin_enqueue_scripts( $hook_suffix ) {
	if ( false === strpos( $hook_suffix, 'ocrmlf' ) )
		return;

	wp_enqueue_style(
		'ocrmlf-admin',
		OCRMLF_PLUGIN_URL .  '/admin/css/styles.css',
		array(), OCRMLF_VERSION,
		'all'
	);

	wp_enqueue_script(
		'ocrmlf-editor',
		OCRMLF_PLUGIN_URL . '/admin/js/form_editor.js',
		array( 'jquery', 'postbox' ),
		OCRMLF_VERSION,
		true
	);

	$current_screen = get_current_screen();

	wp_localize_script('ocrmlf-editor', '_ocrmlf', array(
		'screenId' => $current_screen->id,
	));
}

