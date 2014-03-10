<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) )
	die( '-1' );

?>
<script type="text/javascript">
jQuery(document).ready(function() {
	OneCRMLeadFormEditor.init(<?php echo $post->fields ?>);
});
</script>

<div class="wrap">

<h2><?php
	if ( $post->initial ) {
		echo esc_html( __( 'Add New Lead Form', OCRMLF_TEXTDOMAIN) );
	} else {
		echo esc_html( __( 'Edit Lead Form', OCRMLF_TEXTDOMAIN) );

		echo ' <a href="' . esc_url( menu_page_url( 'ocrmlf-new', false ) ) . '" class="add-new-h2">' . esc_html( __( 'Add New', OCRMLF_TEXTDOMAIN) ) . '</a>';
	}
?></h2>

<?php do_action( 'ocrmlf_admin_notices' ); ?>

<br class="clear" />

<?php
if ( $post ) :

	if ( current_user_can( 'ocrmlf_edit', $post_id ) )
		$disabled = '';
	else
		$disabled = ' disabled="disabled"';
?>

<form method="post" action="<?php echo esc_url( add_query_arg( array( 'post' => $post_id ), menu_page_url( 'ocrmlf', false ) ) ); ?>" id="ocrmlf-admin-form-element"<?php do_action( 'wpcf7_post_edit_form_tag' ); ?>>
	<?php if ( current_user_can( 'ocrmlf_edit', $post_id ) )
		wp_nonce_field( 'ocrmlf-save-contact-form_' . $post_id ); ?>
	<input type="hidden" id="post_ID" name="post_ID" value="<?php echo (int) $post_id; ?>" />
	<input type="hidden" id="hiddenaction" name="action" value="save" />
	<input type="hidden" id="ocrmlf-fields" name="ocrmlf-fields" value="" />

	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2" style="margin-right: 400px;">
<div id="post-body-content">

	<div id="titlediv">
		<input type="text" id="title" name="ocrmlf-title" size="80" value="<?php echo esc_attr( $post->title ); ?>"<?php echo $disabled; ?> />

		<?php if ( ! $post->initial ) : ?>
		<p class="tagcode">
			<?php echo esc_html(__("Copy this code and paste it into your post, page or text widget content.", OCRMLF_TEXTDOMAIN)); ?><br />
			<input type="text" size="30" id="contact-form-anchor-text" onfocus="this.select();" readonly="readonly" class="wp-ui-text-highlight code" value="[onecrm-lead-form id=&quot;<?php echo $post->id?>&quot;]" />
		</p>
		<?php endif; ?>

	</div>

	<h3><?php echo esc_html(__('Form content', OCRMLF_TEXTDOMAIN)) ?></h3>
	<textarea id="ocrmlf-form" name="ocrmlf-form" cols="100" rows="24" style="width:100%"><?php echo esc_textarea( $post->form ); ?></textarea>
		<?php do_meta_boxes( null, 'email', $post ); ?>
		<?php do_meta_boxes( null, 'script', $post ); ?>
		<?php do_meta_boxes( null, 'msg', $post ); ?>
</div>

	<div id="postbox-container-1" class="postbox-container" style="width:380px; margin-right: -400px">
		<?php do_meta_boxes( null, 'buttons', $post ); ?>
		<?php do_meta_boxes( null, 'import', $post ); ?>
		<?php do_meta_boxes( null, 'fields', $post ); ?>
	</div>
	
</div>

<br class="clear />
	</div>

</form>

<?php endif; ?>

</div>
