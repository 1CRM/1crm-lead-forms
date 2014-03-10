<?php

get_current_screen()->add_help_tab(
	array(
		'id'      => 'fields',
		'title'   => __('Creating fields', OCRMLF_TEXTDOMAIN),
		'content' => '<p>' . __('In the <strong>Fields</strong> box, you can add fields to be used in the form. Click the <strong>Add Field</strong> button, and select field type. For each field you specify field name, and a number of options. Most options are self-explanatory. For <strong>Drop-down menu</strong> and <strong>Radio buttons</strong> inputs, you have to specify a list of choices. Each choice is placed on a separate line in corresponding tex box. You can use <strong>|</strong> (pipe symbol) specify both posted value and displayed value for a choice. For example, <strong>option1|Option Value 1</strong> displays <strong>Option Value 1</strong> to user, but external script will receive <strong>option1</strong>. If you do not use the pipe symbol, both posted value and displayed value will be the same.', OCRMLF_TEXTDOMAIN)  . '</p>',
	)
);

get_current_screen()->add_help_tab(
	array(
		'id'      => 'form',
		'title'   => __('Inserting fields', OCRMLF_TEXTDOMAIN),
		'content' => 
			'<p>'
			. __('The content of the form must be entered into <strong>Form content</strong> box. You can use any HTML markup you wish, it will be copied to post, page or widget using [onecrm-lead-form] shortcode. To insert form fields, enter field names in curly brackets. For example, <strong>{emai}</strong> will be replaced with HTML input for <strong>email</strong> field.', OCRMLF_TEXTDOMAIN)
			. '</p>'
			. '<p>'
			. __('You can use <strong>A</strong> button next to field name to insert it into <strong>Form content</strong> box in current cursor position.', OCRMLF_TEXTDOMAIN)
			. '</p>'
			,
	)
);

get_current_screen()->add_help_tab(
	array(
		'id'      => 'email',
		'title'   => __('Sending email', OCRMLF_TEXTDOMAIN),
		'content' => 
			'<p>'
			. __('This plugin can send an email with values the user entered into form. To have an email sent, you specify sender, recipient, subject and email body in corresponding fields, optionally using field  value placeholders, in the same manner as in the form content.', OCRMLF_TEXTDOMAIN)
			. '</p>'
			. '<p>'
			. __('Sending email is optional. If you leave <strong>To</strong> field blank, no email will be sent.', OCRMLF_TEXTDOMAIN)
			. '</p>'
			. '<p>'
			. __('Multiple recipients can be specified in <strong>To</strong> field, separated by comma. Each recipient can be either an email address or a combination of name and address, in the following format: <br /><strong>Jonh Smith &lt;john.smith@example.com&gt;</strong>.', OCRMLF_TEXTDOMAIN)
			. '</p>'
			,
	)
);

get_current_screen()->add_help_tab(
	array(
		'id'      => 'http',
		'title'   => __('Sending data to external script', OCRMLF_TEXTDOMAIN),
		'content' =>
			'<p>'
			. __('Optionally, the form data can be submitted to an external script via HTTP requesti, using either GET or POST method.', OCRMLF_TEXTDOMAIN)
			. '</p>'
			. '<p>'
			. __('The plugin expects the external script to return an object in JSON format, with following fields (all fields are optional): <blockquote><ul>
			<li><i>error</i> : error text. If no error occured, do not return this field</li>
			<li><i>redirect</i> : Redirect URL. Browser will be redirected to this URL after successfull form post. If this field is missing, the value of <strong>Success URL</strong> form field will be used</li>
			<li><i>message</i> : Message to display to user after successfull form post</li>
			</ul>
</blockquote>', OCRMLF_TEXTDOMAIN)
			. '</p>'
			,
	)
);


