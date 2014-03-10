<?php

require_once OCRMLF_INCLUDES_DIR . '/renderer.php';
require_once OCRMLF_INCLUDES_DIR . '/mailer.php';
require_once OCRMLF_INCLUDES_DIR . '/http.php';

class OneCRMLeadForm {
	const POST_TYPE = 'onecrm_lead_form';

	static protected $found_items = 0;
	static protected $current;

	static protected $stored_props = array(
		'fields',
		'mail_from',
		'mail_to',
		'mail_subject',
		'mail_body',
		'url',
		'success_url',
		'method',
		'messages',
	);

	public $initial = false;
	public $id;
	public $name;
	public $title;
	public $form;
	public $fields;
	public $mail_from;
	public $mail_to;
	public $mail_subject;
	public $mail_body;
	public $url;
	public $success_url;
	public $method;
	public $validation_result = array();
	public $input = array();
	public $messages = array();

	private static $message_defaults;
	private static $message_descriptions;

	public function __construct( $post = null ) {

		if (empty(self::$message_defaults)) {
			self::$message_defaults = array(
				'required' => __('Please fill the required field.', OCRMLF_TEXTDOMAIN),
				'invalid_number' => __('Number format seems invalid.', OCRMLF_TEXTDOMAIN),
				'number_min' =>  __('Number is too small.', OCRMLF_TEXTDOMAIN),
				'number_max' =>  __('Number is too large.', OCRMLF_TEXTDOMAIN),
				'email' =>  __('Invalid email format.', OCRMLF_TEXTDOMAIN),
				'url' =>  __('Invalid URL format.', OCRMLF_TEXTDOMAIN),
				'phone' =>  __('Invalid telephone number format.', OCRMLF_TEXTDOMAIN),
				'date' =>  __('Invalid date format.', OCRMLF_TEXTDOMAIN),
				'error_posting' => __('Error posting form. Please try again later.', OCRMLF_TEXTDOMAIN),
				'success' => __('Your message was sent. Thanks.', OCRMLF_TEXTDOMAIN),
			);
			self::$message_descriptions = array(
				'required' => __('A required field was left empty.', OCRMLF_TEXTDOMAIN),
				'invalid_number' => __('Number format is not invalid.', OCRMLF_TEXTDOMAIN),
				'number_min' =>  __('Number is smaller than minimum limit.', OCRMLF_TEXTDOMAIN),
				'number_max' =>  __('Number is larger than maximum limit.', OCRMLF_TEXTDOMAIN),
				'email' =>  __('Email address is not invalid.', OCRMLF_TEXTDOMAIN),
				'url' =>  __('URL is not invalid.', OCRMLF_TEXTDOMAIN),
				'phone' =>  __('Telephone number is not valid.', OCRMLF_TEXTDOMAIN),
				'date' =>  __('Date entry is not valid.', OCRMLF_TEXTDOMAIN),
				'error_posting' => __('An error occured when posting the form.', OCRMLF_TEXTDOMAIN),
				'success' => __('Messages sent successfully.', OCRMLF_TEXTDOMAIN),
			);
		}
		$this->initial = true;
		$this->form = '';
        $this->url = get_option('ocrmlf_default_url');
		$post = get_post( $post );

		if ( $post && self::POST_TYPE == get_post_type($post) ) {
			$this->initial = false;
			$this->id = $post->ID;
			$this->name = $post->post_name;
			$this->title = $post->post_title;
			$this->form = $post->post_content;

			foreach (self::$stored_props as $k)
				$this->$k = get_post_meta($post->ID, 'ocrmlf_' . $k, true);
		}
	}

	public function save() {
		if ( $this->initial ) {
			$post_id = wp_insert_post( array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'post_title' => $this->title,
				'post_content' => trim($this->form)));
		} else {
			$post_id = wp_update_post( array(
				'ID' => (int) $this->id,
				'post_status' => 'publish',
				'post_title' => $this->title,
				'post_content' => trim($this->form)));
		}
		if ($post_id) {
			foreach (self::$stored_props as $k)
				update_post_meta($post_id, 'ocrmlf_' . $k, $this->$k);
		}
		return $post_id;
	}
	
	public function set_validation_result($result) {
		$this->validation_result = $result;
	}

	public function set_input($input) {
		$this->input = $input;
	}

	public function copy() {
		$new = new self;
		$new->initial = true;
		$new->title = $this->title . '_copy';
		$new->form = $this->form;
		$new = apply_filters_ref_array( 'ocrmlf_copy', array( &$new, &$this ) );
		return $new;
	}

	public function delete() {
		if ( $this->initial )
			return;

		if ( wp_delete_post( $this->id, true ) ) {
			$this->initial = true;
			$this->id = null;
			return true;
		}

		return false;
	}

	public static function set_current(self $obj) {
		self::$current = $obj;
	}

	public static function get_current() {
		return self::$current;
	}

	public static function register_post_types() {
		register_post_type(
			self::POST_TYPE, 
			array(
				'labels' => array(
					'name' => __( 'Lead Forms', OCRMLF_TEXTDOMAIN ),
					'singular_name' => __( 'Lead Form', OCRMLF_TEXTDOMAIN),
				),
				'rewrite' => false,
				'query_var' => false,
			)
		);
		add_shortcode( 'onecrm-lead-form', array('OneCRMLeadFormRenderer', 'shortcode_func' ));
	}

	public static function count() {
		return self::$found_items;
	}

	public static function find($args = '') {
		$defaults = array(
			'post_status' => 'any',
			'posts_per_page' => -1,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC' );

		$args = wp_parse_args($args, $defaults);

		$args['post_type'] = self::POST_TYPE;

		$q = new WP_Query();
		$posts = $q->query( $args );

		self::$found_items = $q->found_posts;

		$objs = array();

		foreach ( (array) $posts as $post )
			$objs[] = new self( $post );

		return $objs;
	}

	public function submit($data, &$response) {
		$mailer = new OneCRMLeadFormMailer($this, $data);
		$result = $mailer->mail();
		$response['mail'] = $result;
		$http = new OneCRMLeadFormHTTP($this, $data);
		$http->send($response);
		if (strlen($this->success_url))
			$response['redirect'] = $this->success_url;
	}

	public function add_js_messages() {
		$messages = $this->get_messages();
		wp_localize_script('ocrmlf-forms', '_ocrmlf_messages_' . $this->id, $messages);
	}

	public function get_messages($add_descriptions = false) {
		$messages = self::$message_defaults;
		$descriptions = self::$message_descriptions;
		foreach ($messages as $k => &$msg) {
			if (isset($this->messages[$k]))
				$msg = $this->messages[$k];
			if ($add_descriptions)
				$msg = array($msg, $descriptions[$k]);
		}
		return $messages;
	}

}

