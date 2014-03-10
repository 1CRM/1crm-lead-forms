<?php

if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

require_once OCRMLF_INCLUDES_DIR . '/leadform.php';

class OneCRMLeads_List_Table extends WP_List_Table {

	public static function define_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Title', OCRMLF_TEXTDOMAIN),
			'shortcode' => __('Shortcode', OCRMLF_TEXTDOMAIN),
			'author' => __('Author', OCRMLF_TEXTDOMAIN),
			'date' => __('Date', OCRMLF_TEXTDOMAIN)
		);
		return $columns;
	}

	function __construct() {
		parent::__construct( array(
			'singular' => 'post',
			'plural' => 'posts',
			'ajax' => false ) );
	}

	function prepare_items() {
		$current_screen = get_current_screen();
		$per_page = $this->get_items_per_page( 'ocrmlf_per_page' );

		$this->_column_headers = $this->get_column_info();

		$args = array(
			'posts_per_page' => $per_page,
			'orderby' => 'title',
			'order' => 'ASC',
			'offset' => ( $this->get_pagenum() - 1 ) * $per_page );

		if ( ! empty( $_REQUEST['s'] ) )
			$args['s'] = $_REQUEST['s'];

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			if ( 'title' == $_REQUEST['orderby'] )
				$args['orderby'] = 'title';
			elseif ( 'author' == $_REQUEST['orderby'] )
				$args['orderby'] = 'author';
			elseif ( 'date' == $_REQUEST['orderby'] )
				$args['orderby'] = 'date';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			if ( 'asc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'ASC';
			elseif ( 'desc' == strtolower( $_REQUEST['order'] ) )
				$args['order'] = 'DESC';
		}

		$this->items = OneCRMLeadForm::find($args);

		$total_items = OneCRMLeadForm::count();
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'total_pages' => $total_pages,
			'per_page' => $per_page ) );
	}

	function get_columns() {
		return get_column_headers( get_current_screen() );
	}

	function get_sortable_columns() {
		$columns = array(
			'title' => array( 'title', true ),
			'author' => array( 'author', false ),
			'date' => array( 'date', false ) );

		return $columns;
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => __('Delete', OCRMLF_TEXTDOMAIN));

		return $actions;
	}

	function column_default( $item, $column_name ) {
		return '';
    }

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->id );
	}

	function column_title( $item ) {
		$url = admin_url( 'admin.php?page=ocrmlf&post=' . absint( $item->id ) );
		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

		$actions = array(
			'edit' => '<a href="' . $edit_link . '">' . __('Edit', OCRMLF_TEXTDOMAIN) . '</a>' );

		if ( current_user_can('ocrmlf_edit', $item->id)) {
			$copy_link = wp_nonce_url(
				add_query_arg( array( 'action' => 'copy' ), $url ),
				'ocrmlf-copy-contact-form_' . absint( $item->id ) );

			$actions = array_merge( $actions, array(
				'copy' => '<a href="' . $copy_link . '">' . __('Copy', OCRMLF_TEXTDOMAIN) . '</a>' ) );
		}

		$a = sprintf( '<a class="row-title" href="%1$s" title="%2$s">%3$s</a>',
			$edit_link,
			esc_attr( sprintf(__('Edit &#8220;%s&#8221;', OCRMLF_TEXTDOMAIN), $item->title ) ),
			esc_html( $item->title ) );

		return '<strong>' . $a . '</strong> ' . $this->row_actions( $actions );
    }

	function column_author( $item ) {
		$post = get_post( $item->id );

		if ( ! $post )
			return;

		$author = get_userdata( $post->post_author );

		return esc_html( $author->display_name );
    }

	function column_shortcode( $item ) {
		$shortcodes = array(
			sprintf( '[onecrm-lead-form id="%1$d"]', $item->id, $item->title ) );

		$output = '';

		foreach ( $shortcodes as $shortcode ) {
			$output .= "\n" . '<input type="text"'
				. ' onfocus="this.select();" readonly="readonly"'
				. ' value="' . esc_attr( $shortcode ) . '"'
				. ' class="shortcode-in-list-table wp-ui-text-highlight code" />';
		}

		return trim( $output );
	}

	function column_date( $item ) {
		$post = get_post( $item->id );

		if ( ! $post )
			return;

		$t_time = mysql2date( __('Y/m/d g:i:s A', OCRMLF_TEXTDOMAIN), $post->post_date, true);
		$m_time = $post->post_date;
		$time = mysql2date( 'G', $post->post_date ) - get_option( 'gmt_offset' ) * 3600;

		$time_diff = time() - $time;

		if ( $time_diff > 0 && $time_diff < 24*60*60 )
			$h_time = sprintf( __('%s ago', OCRMLF_TEXTDOMAIN), human_time_diff( $time ) );
		else
			$h_time = mysql2date(__('Y/m/d', OCRMLF_TEXTDOMAIN), $m_time);

		return '<abbr title="' . $t_time . '">' . $h_time . '</abbr>';
    }
}

?>
