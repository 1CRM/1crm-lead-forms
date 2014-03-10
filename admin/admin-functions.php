<?php

function ocrmlf_current_action() {
	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
		return $_REQUEST['action'];

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
		return $_REQUEST['action2'];

	return false;
}

function ocrmlf_get_current_form() {
	if ( $current = OneCRMLeadForm::get_current() ) {
		return $current;
	}
}


add_action( 'admin_menu', 'ocrmlf_plugin_menu' );
add_action( 'admin_init', 'register_ocrmlf_settings' );

function register_ocrmlf_settings() {
	register_setting( 'ocrmlf_options_group', 'ocrmlf_default_url' ); 
} 


function ocrmlf_plugin_menu() {
	add_options_page( '1CRM Lead Forms Options', '1CRM Forms', 'manage_options', 'ocrmlf_options', 'ocrmlf_plugin_options' );
}

function ocrmlf_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="wrap">';
?>
<h2>1CRM Lead Forms Options</h2>
<form method="post" action="options.php"> 
<?php settings_fields( 'ocrmlf_options_group' );
do_settings_sections('ocrmlf_options_group');
?>
<table class="form-table">
	<tr valign="top">
		<th scope="row">Default external script URL</th>
        <td><input type="text" name="ocrmlf_default_url" value="<?php echo get_option('ocrmlf_default_url'); ?>" /></td>
	</tr>
</table>
<?php submit_button(); ?>
</form>

<?php
	echo '</div>';
}



function ocrmlf_add_meta_boxes( $post_id ) {
	add_meta_box( 'fieldsdiv', __( 'Fields', OCRMLF_TEXTDOMAIN),
		'ocrmlf_fields_meta_box', null, 'fields', 'core');
	add_meta_box( 'buttonsdiv', __( 'Actions', OCRMLF_TEXTDOMAIN),
		'ocrmlf_buttons_meta_box', null, 'buttons', 'core');
	add_meta_box( 'emaildiv', __( 'Email', OCRMLF_TEXTDOMAIN),
		'ocrmlf_email_meta_box', null, 'email', 'core');
	add_meta_box( 'scriptdiv', __( 'Options', OCRMLF_TEXTDOMAIN),
		'ocrmlf_script_meta_box', null, 'script', 'core');
	add_meta_box( 'msgdiv', __( 'Messages', OCRMLF_TEXTDOMAIN),
		'ocrmlf_msg_meta_box', null, 'msg', 'core');
	add_meta_box( 'importdiv', __( 'Import', OCRMLF_TEXTDOMAIN),
		'ocrmlf_import_meta_box', null, 'import', 'core');

	add_filter( 'postbox_classes_toplevel_page_ocrmlf_msgdiv', 'ocrmlf_close_metabox');
	add_filter( 'postbox_classes_toplevel_page_ocrmlf_scriptdiv', 'ocrmlf_close_metabox');
	add_filter( 'postbox_classes_toplevel_page_ocrmlf_emaildiv', 'ocrmlf_close_metabox');
	add_filter( 'postbox_classes_toplevel_page_ocrmlf_importdiv', 'ocrmlf_close_metabox');
}

function ocrmlf_close_metabox($classes)
{
	$classes[] = 'closed';
	return $classes;
}

function ocrmlf_fields_meta_box($post) {
	$types = array(
		'text' => 'Text field',
		'email' => 'Email',
		'url' => 'URL',
		'phone' => 'Telephone number',
		'spinbox' => 'Number (spinbox)',
		'slider' => 'Number (slider)',
		'date' => 'Date',
		'textarea' => 'Text area',
		'select' => 'Drop-down menu',
		'checkbox' => 'Checkbox',
		'radio' => 'Radio buttons',
		'hidden' => 'Hidden field',
		'submit' => 'Submit button',
	);
?>
	<input onclick="jQuery('#field_types_list').toggle();" type="button" class="button" value="<?php echo esc_html(__('Add Field', OCRMLF_TEXTDOMAIN)) ?>">
	<div id="field_types_list" class="field-list-dropdown">
		<?php foreach ($types as $type=> $label) : ?>
		<div data-id="<?php echo $type?>"><?php echo esc_html(__($label)) ?></div>
		<?php endforeach ?>
	</div>
	<div id="fields-container">
	</div>

<?php
}


function ocrmlf_buttons_meta_box($post) { ?>
	<?php if (current_user_can( 'ocrmlf_edit', $post->id)) : ?>
		<input type="submit" onclick="return OneCRMLeadFormEditor.validate()" class="button-primary" name="ocrmlf-save" value="<?php echo esc_attr( __( 'Save', OCRMLF_TEXTDOMAIN)); ?>" />
	<?php endif; ?>

	<?php if ( current_user_can( 'ocrmlf_edit', $post->id ) && ! $post->initial ) : ?>
		<?php $copy_nonce = wp_create_nonce( 'ocrmlf-copy-contact-form_' . $post->id ); ?>
		<input type="submit" name="ocrmlf-copy" class="button" value="<?php echo esc_attr( __( 'Duplicate', OCRMLF_TEXTDOMAIN)); ?>"
		<?php echo "onclick=\"this.form._wpnonce.value = '$copy_nonce'; this.form.action.value = 'copy'; return true;\""; ?> />
		<?php $delete_nonce = wp_create_nonce( 'ocrmlf-delete-contact-form_' . $post->id ); ?>
		<input type="submit" name="ocrmlf-delete" class="button" value="<?php echo esc_attr( __( 'Delete', OCRMLF_TEXTDOMAIN)); ?>"
		<?php echo "onclick=\"if (confirm('" .
			esc_js(__( "You are about to delete this contact form.\n  'Cancel' to stop, 'OK' to delete.", OCRMLF_TEXTDOMAIN)) .
			"')) {this.form._wpnonce.value = '$delete_nonce'; this.form.action.value = 'delete'; return true;} return false;\""; ?> />
	<?php endif; ?>
<?php
}

function ocrmlf_email_meta_box($post) { ?>
<div>
	<div>
		<span class="ocrmlf-label">To</span>
		<span class="ocrmlf-field"><input type="text" name="ocrmlf-to" value="<?php echo esc_attr($post->mail_to)?>" /></span>
	</div>
	<div>
		<span class="ocrmlf-label">From</span>
		<span class="ocrmlf-field"> <input type="text" name="ocrmlf-from" value="<?php echo esc_attr($post->mail_from)?>" /></span>
	</div>
	<div>
		<span class="ocrmlf-label">Subject</span>
		<span class="ocrmlf-field"> <input type="text" name="ocrmlf-subject" value="<?php echo esc_attr($post->mail_subject)?>" /></span>
	</div>
	<br />
	Text:<br />
	<textarea style="width: 100%; height: 200px" name="ocrmlf-body" id="ocrmlf-body"><?php echo esc_html($post->mail_body)?></textarea>
</div>
<?php
}

function ocrmlf_script_meta_box($post) {
?>
<div>
	<div>
		<span class="ocrmlf-label"><?php echo esc_html(__('External script URL', OCRMLF_TEXTDOMAIN))?></span>
		<span class="ocrmlf-field"><input type="text" name="ocrmlf-url" id="ocrmlf-url" value="<?php echo esc_attr($post->url)?>" /></span>
	</div>
	<div>
		<span class="ocrmlf-label"><?php echo esc_html(__('HTTP method', OCRMLF_TEXTDOMAIN)) ?></span>
		<span class="ocrmlf-field">
			<select name="ocrmlf-method">
				<option value="POST">POST</option>
				<option value="GET" <?php if ($post->method == 'GET') echo ' selected="selected"' ?>>GET</option>
			</select>
		</span>
	</div>
	<div>
		<span class="ocrmlf-label"><?php echo esc_html(__('Success URL', OCRMLF_TEXTDOMAIN))?></span>
		<span class="ocrmlf-field"><input type="text" name="ocrmlf-success-url" value="<?php echo esc_attr($post->success_url)?>" /></span>
	</div>
</div>
<?php
}

function ocrmlf_msg_meta_box($post) {
	$messages = $post->get_messages(true);
	foreach ($messages as $k => $msg) { ?>
		<div style="font-style:italic"><?php echo esc_html($msg[1]) ?></div>
		<input type="text" style="width:100%" name="ocrmlf-msg[<?php echo $k?>]" value="<?php echo esc_html($msg[0]) ?>" />
<?php
	}
}

function ocrmlf_import_meta_box($post) { ?>
<textarea style="width:100%" rows="8" id="ocrmlf-import-box"></textarea>
<input type="button" onclick="OneCRMLeadFormEditor.import(); return false;" class="button" name="ocrmlf-import" value="<?php echo esc_attr( __( 'Import', OCRMLF_TEXTDOMAIN)); ?>" />

<?php
}
