<?php

class OneCRMLeadFormRenderer {

	private $input;

	static function shortcode_func($attrs, $content = null) {
		$form = OneCRMLeadForm::get_current();
		if (!$form || $form->id != @$attrs['id'])
			$form = new OneCRMLeadForm(@$attrs['id']);
		if ($form->initial)
			return __('Form not found!', OCRMLF_TEXTDOMAIN);
		$renderer = new self($form, $form->input);
		return $renderer->render();	
	}

	private function __construct($form, $input) {
		$this->form = $form;
		$this->input = $input;
		$this->fields = json_decode($this->form->fields);
	}

	private function get_input_value($name) {
		return isset($this->input[$name]) ? $this->input[$name] : null;
	}

	public function render() {
		$re = '~\{([a-z][0-9A-Z:._-]*)\}~i';
		$uri = ocrmlf_request_uri();
		$id = 'ocrmlf-form-' . $this->form->id;
		$html = '<form method="POST" class="ocrmlf-form" id="' . $id . '" action="' . $uri . '">';
		$html .= '<input type="hidden" name="_ocrmlf_form_id" value="' . $id . '" >';
		$html .= '<input type="hidden" name="_ocrmlf_id" value="' . $this->form->id . '" >';
		$vars = ocrm_lf_process_request_vars();
		foreach ($vars as $k => $v) {
			$html .= '<input type="hidden" name="' . $k . '" value="' . $v . '" >';
		}
		$this->replaced_tags = array();
		$html .= preg_replace_callback($re, array($this, 'replace_tags'), $this->form->form);

		$message = null;
		if (isset($this->form->validation_result['http_result']->error)) {
			$message = $this->form->validation_result['http_result']->error;
			$style = ' style="display:block" role="alert" ';
		} elseif (!$this->has_errors() && isset($this->form->validation_result['http_result']->message)) {
			$message = $this->form->validation_result['http_result']->message;
			$style = ' style="display:block" ';
		} elseif (!$this->has_errors() && !empty($this->form->validation_result['posted'])) {
			$messages = $this->form->get_messages();
			$message = $messages['success'];
			$style = ' style="display:block" ';

		}

		$html .= '<div class="ocrmlf-form-status"' . $style .'>' . esc_html($message) . '</div>';
		$html .= '</form>';

		$this->form->add_js_messages();

		return $html;
	}

	public function replace_tags($matches) {
		$def = $this->find_field_definition($matches[1]);
		if (!$def)
			return '';

		$ret =  $this->{'render_' . $def->type}($def);
		$name = $def->name;
		if (!empty($def->multiple))
			$name .= '[]';
		$messages = $this->form->get_messages();
		if (isset($this->form->validation_result['errors'][$name])) {
			$msg = $this->form->validation_result['errors'][$name];
			if (isset($messages[$msg]))
				$msg = $messages[$msg];
			$ret .= '<span role="alert" class="ocrmlf-invalid-field">' . esc_html($msg) . '</span>';
		}
		return $ret;
	}

	private function find_field_definition($name) {
		foreach ($this->fields as $f)
			if ($f->name == $name)
				return $f;
	}
	
	private function render_hidden($def) {
		$attrs = array(
			'type' => 'hidden',
			'value' => '' . @$def->value,
		);
		return $this->render_input($def, $attrs);
	}

	private function render_slider($def) {
		$attrs = array(
			'type' => 'range',
			'class' => 'ocrmlf-input text range',
		);
		return $this->render_input($def, $attrs);
	}

	private function render_phone($def) {
		$attrs = array(
			'type' => 'tel',
			'class' => 'ocrmlf-input text tel',
		);
		return $this->render_input($def, $attrs);
	}

	private function render_url($def) {
		$attrs = array(
			'type' => 'url',
			'class' => 'ocrmlf-input text url',
		);
		return $this->render_input($def, $attrs);
	}

	private function render_spinbox($def) {
		$attrs = array(
			'type' => 'number',
			'class' => 'ocrmlf-input text number',
		);
		return $this->render_input($def, $attrs);
	}

	private function render_email($def) {
		$attrs = array(
			'type' => 'email',
			'class' => 'ocrmlf-input text email',
		);
		return $this->render_input($def, $attrs);
	}

	private function render_date($def) {
		$attrs = array(
			'type' => 'date',
			'class' => 'ocrmlf-input text date',
		);
		return $this->render_input($def, $attrs);
	}

	private function render_textarea($def) {
		$attrs = array(
			'class' => 'ocrmlf-input textarea',
		);
		$ret = $this->render_input($def, $attrs, true);
		$value = $this->has_errors() ? $this->get_input_value($def->name) : null;
		if (is_null($value) && isset($def->default))
			$value = $def->default;
		$ret .= esc_html($value);
		$ret .= '</textarea>';
		return $ret;

	}

	private function render_radio($def) {
		$attrs = array(
			'type' => 'radio',
			'class' => 'ocrmlf-input radio',
		);
		$options = $this->parse_options(@$def->choices);
		$buttons = array();
		$value = $this->has_errors() ? $this->get_input_value($def->name) : null;
		foreach ($options as $k => $v) {
			$attrs['value'] = $k;
			if ($value == $k)
				$attrs['checked'] = 'checked';
			else
				unset($attrs['checked']);
			$str = $this->render_input($def, $attrs);
			if (!empty($def->label_first))
				$str = esc_html($v) . ' ' . $str;
			else
				$str .= ' ' . esc_html($v);
			if (!empty($def->use_label))
				$str = '<label>' . $str . '</label>';
			$buttons[] = $str;
		}
		$sep = isset($def->separator) ? $def->separator : '<br />';
		return join($sep, $buttons);
	}

	private function render_select($def) {

		$attrs = array(
			'class' => 'ocrmlf-input select',
			'name' => $def->name . (empty($def->multiple) ? '' : '[]'),
		);
		if (isset($def->multiple))
			$attrs['multiple'] = 'multiple';
		if (isset($def->class))
			$attrs['class'] .= ' ' . $def->class;
		if (!empty($def->required))
			$attrs['class'] .= ' ocrmlf-required';
		if (!empty($def->id))
			$attrs['id'] = $def->id;
		$html = $this->make_tag('select', $attrs, false);
		$options = array();
		if (!empty($def->add_blank))
			$options[''] = '';
		$options = $this->parse_options(@$def->choices, $options);
		$value = $this->has_errors() ? $this->get_input_value($def->name) : null;
		if (!empty($def->multiple))
			$value = (array)$value;
		foreach ($options as $k => $v) {
			$attrs = array(
				'value' => $k,
			);
			if (empty($def->multiple) ? ($value == $k) : in_array($k, $value))
				$attrs['selected'] = 'selected';
			else
				unset($attrs['selected']);
			$html .= $this->make_tag('option', $attrs, false);
			$html .= esc_html($v);
			$html .= '</option>';
		}
		$html .= '</select>';
		return $html;
	}

	private function render_checkbox($def) {
		$attrs = array(
			'type' => 'checkbox',
			'value' => 1,
			'class' => 'ocrmlf-input text',
		);
		$value = $this->has_errors() ? $this->get_input_value($def->name) : null;
		if ($value)
			$attrs['checked'] = 'checked';
		$ret = $this->render_input($def, $attrs);
		if (isset($def->label) && strlen($def->label)) {
			if (!empty($def->label_first))
				$ret = esc_html($def->label) . ' ' . $ret;
			else
				$ret .= ' ' . esc_html($def->label);
		}
		
		if (!empty($def->use_label))
			$ret = '<label>' . $ret . '</label>';

		return $ret;
	}

	private function render_submit($def) {
		$attrs = array(
			'type' => 'submit',
			'class' => 'ocrmlf-input submit',
		);
		if (isset($def->label) && strlen($def->label))
			$attrs['value'] = $def->label;
		if (isset($def->wait_label) && strlen($def->wait_label))
			$attrs['wait_value'] = $def->wait_label;
		return $this->render_input($def, $attrs);
	}

	private function render_text($def) {
		$attrs = array(
			'type' => 'text',
			'class' => 'ocrmlf-input text',
		);
		return $this->render_input($def, $attrs);
	}

	private function render_input($def, $attrs, $textarea = false) {
		static $extra_attrs = array(
			'max', 'min', 'step', 'maxlen', 'size', 'id', 'cols', 'rows',
		);
		$attrs['name'] = $def->name;
		if (isset($def->placeholder))
			$attrs['placeholder'] = $def->placeholder;
		if (!$textarea && !isset($attrs['value']) && $def->type != 'submit') {
			$value = $this->has_errors() ? $this->get_input_value($def->name) : null;
			if (is_null($value) && isset($def->default))
				$value = $def->default;
			$attrs['value'] = $value;
		}
		if (isset($def->class))
			@$attrs['class'] .= ' ' . $def->class;
		if (!empty($def->required))
			@$attrs['class'] .= ' ocrmlf-required';
		foreach ($extra_attrs as $a)
			if (isset($def->$a))
				$attrs[$a] = $def->$a;
		return $this->make_tag($textarea ? 'textarea' : 'input', $attrs, !$textarea);
	}

	private function make_tag($name, $attrs = array(), $close = false) {
		$ret = '<' . $name . ' ';
		foreach ($attrs as $a => $v)
			$ret .= $a . '="' . esc_attr($v) . '" ';
		if ($close)
			$ret .= '/';
		$ret .= '>';
		return $ret;
	}

	private function parse_options($str, $ret = array()) {
		$lines = array_filter(array_map('trim', preg_split('~[\r\n]+~', $str)));
		foreach ($lines as $line) {
			$parts = explode('|', $line, 2);
			if (count($parts) == 2)
				$ret[$parts[0]] = $parts[1];
			else
				$ret[$parts[0]] = $parts[0];
		}
		return $ret;
	}

	private function has_errors() {
		return isset($this->form->validation_result['errors']) || isset($this->form->validation_result['http_result']->error);
	}
}	

