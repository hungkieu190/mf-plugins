<?php
/**
 * Template for wp-editor field in LearnPress settings.
 *
 * @var array $field Field configuration
 */


if (!defined('ABSPATH')) {
	exit;
}

$field = $value;


$field_key = isset($field['id']) ? $field['id'] : '';
$field_value = isset($field['value']) ? $field['value'] : '';
$field_default = isset($field['default']) ? $field['default'] : '';
$field_desc = isset($field['desc']) ? $field['desc'] : '';
$field_args = isset($field['args']) ? $field['args'] : array();
$field_title = isset($field['title']) ? $field['title'] : '';
$field_class = isset($field['class']) ? $field['class'] : '';

// Use default if value is empty
if (empty($field_value) && !empty($field_default)) {
	$field_value = $field_default;
}

// Create unique editor ID to avoid conflicts
$editor_id = str_replace(array('[', ']'), array('_', ''), $field_key);

// Default editor args
$editor_args = wp_parse_args(
	$field_args,
	array(
		'textarea_name' => $field_key,
		'textarea_rows' => 10,
		'wpautop' => true,
		'media_buttons' => true,
		'teeny' => false,
		'tinymce' => array(
			'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,forecolor,undo,redo',
			'toolbar2' => '',
		),
		'quicktags' => array(
			'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,close',
		),
	)
);

?>
<tr valign="top" class="<?php echo esc_attr($field_class); ?>">
	<th scope="row" class="titledesc">
		<label for="<?php echo esc_attr($editor_id); ?>">
			<?php echo esc_html($field_title); ?>
		</label>
	</th>
	<td class="forminp forminp-<?php echo esc_attr(sanitize_title($field['type'])); ?>">
		<div class="lp-uppc-wp-editor-wrapper">
			<?php
			wp_editor($field_value, $editor_id, $editor_args);
			?>
		</div>
		<?php if (!empty($field_desc)): ?>
			<p class="description"><?php echo wp_kses_post($field_desc); ?></p>
		<?php endif; ?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				// Ensure TinyMCE is properly initialized
				if (typeof tinymce !== 'undefined') {
					var editorId = '<?php echo esc_js($editor_id); ?>';

					// Remove existing editor instance if any
					if (tinymce.get(editorId)) {
						tinymce.get(editorId).remove();
					}

					// Reinitialize editor after a short delay
					setTimeout(function () {
						if (typeof tinyMCEPreInit !== 'undefined' && tinyMCEPreInit.mceInit[editorId]) {
							tinymce.init(tinyMCEPreInit.mceInit[editorId]);
						}
					}, 100);
				}

				// Ensure textarea is accessible for non-visual mode
				var textarea = $('#' + '<?php echo esc_js($editor_id); ?>');
				if (textarea.length) {
					textarea.prop('readonly', false);
				}
			});
		</script>
	</td>
</tr>