<?php

require_once OCRMLF_INCLUDES_DIR . '/leadform.php';

class OneCRMLeadFormHTTP {

	private $form;
	private $fields;
	private $input;

	public function __construct(OneCRMLeadForm $form, $input) {
		$this->form = $form;
		$this->input = $input;
		$this->fields = json_decode($this->form->fields);
	}

	public function send(&$response) {

		$url = $this->form->url;
		if (!$url)
			return;

		$ch = curl_init();
		if ($this->form->method == 'GET') {
			$url = $this->append_url_fields($url, $this->input);
		} else {
			$post_str = $this->build_query_str($this->input);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$return = curl_exec($ch);
		curl_close($ch);

		$json = @json_decode($return);
		$response['http_result'] = $json;
	}

	private function append_url_fields($url, $vars) {
		$parsed = parse_url($url);
		$query_vars = array();
		if (isset($parsed['query']))
			parse_str($parsed['query'], $query_vars);
		if (isset($parsed['scheme']))
			$url = $parsed['scheme'] . '://';
		else
			$url = 'http://';
		if (isset($parsed['user']) || isset($parsed['user'])) {
			if (isset($parsed['user']))
				$url .= $parsed['user'];
			if (isset($parsed['pass']))
				$url .= ':' . $parsed['pass'];
			$url .= '@';
		}
		if (isset($parsed['host']))
			$url .= $parsed['host'];
		if (isset($parsed['port']))
			$url .= ':' . $parsed['port'];
		if (isset($parsed['path']))
			$url .= $parsed['path'];
		$query = $this->build_query_str(array_merge($query_vars, $vars));
		if (strlen($query))
			$url .= '?' . $query;
		if (strlen($parsed['fragment']))
			$url .= '#' . $parsed['fragment'];
		return $url;
	}

	private function flatten($key, $arr) {
		$s = '';
		foreach ($arr as $k => $v) {
			if (strlen($s))
				$s .= '&';
			if (is_array($v)) {
				$s .= $this->flatten($key . '[' . $k . ']', $v);
			} else {
				$s .= urlencode($key . '[' . $k . ']') . '=' . urlencode($v);
			}
		}
		return $s;
	}

	private function build_query_str($vars) {
		$q = '';
		foreach ($vars as $k => $v) {
			if (strlen($q))
				$q .= '&';
			if (is_array($v)) {
				$q .= $this->flatten($k, $v);
			} else {
				$q .= urlencode($k) . '=' . urlencode($v);
			}
		}
		return $q;
	}
}
