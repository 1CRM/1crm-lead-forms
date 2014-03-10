<div class="wrap">
<?php screen_icon(); ?>

<h2>
	<?php echo esc_html(__( 'Lead Forms', OCRMLF_TEXTDOMAIN)) ?>
	<a href="<?php echo esc_url(menu_page_url('ocrmlf-new', false))?>" class="add-new-h2">
		<?php echo esc_html(__( 'Add New', OCRMLF_TEXTDOMAIN))?>
	</a>
<?php /*
	if ( ! empty( $_REQUEST['s'] ) ) {
		echo sprintf( '<span class="subtitle">'
			. __( 'Search results for &#8220;%s&#8221;', 'contact-form-7' )
			. '</span>', esc_html( $_REQUEST['s'] ) );
	}
 */
?>
</h2>

<?php $list_table = new OneCRMLeads_List_Table; $list_table->prepare_items(); ?>

<form method="get" action="">
	<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
	<?php $list_table->search_box(__( 'Search Lead Forms', OCRMLF_TEXTDOMAIN), 'ocrmlf' ); ?>
	<?php $list_table->display(); ?>
</form>

</div>
