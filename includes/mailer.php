<?php

require_once OCRMLF_INCLUDES_DIR . '/leadform.php';

class OneCRMLeadFormMailer {

	private $form;
	private $fields;
	private $input;

	public function __construct(OneCRMLeadForm $form, $input) {
		$this->form = $form;
		$this->input = $input;
		$this->fields = json_decode($this->form->fields);
	}

	public function mail() {
		$body = $this->substitute($this->form->mail_body);
		$from = $this->substitute($this->form->mail_from, true);
		$to = $this->substitute($this->form->mail_to, true);
		$subject = $this->substitute($this->form->mail_subject, true);
		$headers = "From: $from\n";
		if ($to)
			return wp_mail($to, $subject, $body, $headers) ? true : false;
		return null;
	}

	private function find_field_definition($name) {
		foreach ($this->fields as $f)
			if ($f->name == $name)
				return $f;
	}

	private function substitute($str, $no_nl = false) {
		$re = '~\{([a-z][0-9A-Z:._-]*)\}~i';
		$str = preg_replace_callback($re, array($this, 'replace_tags'), $str);
		if ($no_nl)
			$str = trim(str_replace(array("\r", "\n"), '', $str));
		return $str;
	}

	public function replace_tags($matches) {
		$name = $matches[1];
		$def = $this->find_field_definition($name);
		if (!$def)
			return '';
		switch ($def->type) {
			case 'select':
				return $this->select_values($def);
			case 'radio':
				return $this->radio_value($def);
			case 'checkbox':
				return $this->checkbox_value($def);
		}
		return $this->get_field_value($def->name);
	}

	private function get_field_value($name) {
		return isset($this->input[$name]) ? $this->input[$name] : null;
	}

	private function select_values($field) {
		$name = $field->name;
		$value = $this->get_field_value($name);
		if (!empty($field->multiple))
			$value = (array)$value;
		$options = $this->parse_options(@$field->choices);
		if (empty($field->multiple))
			return isset($options[$value]) ? $options[$value] : $value;
		$ret = array();
		foreach ($value as $v) 
			$ret[] = isset($options[$v]) ? $options[$v] : $v;
		return join(', ', $ret);
	}

	private function radio_value($field) {
		$name = $field->name;
		$value = $this->get_field_value($name);
		$options = $this->parse_options(@$field->choices);
		return isset($options[$value]) ? $options[$value] : $value;
	}

	private function checkbox_value($field) {
		$value = $this->get_field_value($field->name);
		return __($value ? 'Yes' : 'No', OCRMLF_TEXTDOMAIN);
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
}
