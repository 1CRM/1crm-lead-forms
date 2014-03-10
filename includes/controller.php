<?php


require_once OCRMLF_INCLUDES_DIR . '/validator.php';
add_action( 'init', 'ocrmlf_controller_init', 12);

wp_enqueue_style(
	'ocrmlf-style',
	OCRMLF_PLUGIN_URL .  '/includes/css/styles.css',
	array(), OCRMLF_VERSION,
	'all'
);

function ocrmlf_controller_init()
{
	ocrmlf_controller_json();
	ocrmlf_controller_submit();
}

function ocrmlf_controller_json() {
	if (empty($_POST['_ocrmlf_ajax']))
		return;
	if (isset($_POST['_ocrmlf_id'])) {
		if ($form = ocrmlf_lead_form((int)$_POST['_ocrmlf_id'])) {
			$validator = new OneCRMLeadFormValidator($form);
			$response = $validator->validate($_POST);
			if ($response['valid'])
				$form->submit($_POST, $response);
			@header('Content-Type: application/json; charset=' . get_option('blog_charset'));
			echo json_encode($response);
			exit;
		}
	}
}

function ocrmlf_controller_submit() {
	if (empty($_POST['_ocrmlf_id']) || !empty($_POST['_ocrmlf_ajax']))
		return;
	
	if ($form = ocrmlf_lead_form((int)$_POST['_ocrmlf_id'])) {
		$validator = new OneCRMLeadFormValidator($form);
		$input = $_POST;
		$response = $validator->validate($input);
		if ($response['valid'])
			$form->submit($_POST, $response);
		$form->set_validation_result($response);
		$form->set_input($input);
		OneCRMLeadForm::set_current($form);
	}
}

