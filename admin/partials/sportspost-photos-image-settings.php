<?php
/**
 * The template for image settings in the media dialog.
 *
 * @since   2.0.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin/partials
 */
?>
	<script type="text/html" id="tmpl-sportspostimage-settings">
		<div class="attachment-info">
			<h3>{{{ data.selected_image.title }}}</h3>
			<span id="sportspostload" class="spinner" style="display: block"></span>
			<input id="full-sportspost" type="hidden" value="{{ data.selected_image.dataset.full }}" />
			<input id="sportspost-id" type="hidden" value="{{ data.selected_image.id }}" />
			<div class="thumbnail">
			</div>
		</div>
		<?php do_action('sportspost_settings_before'); ?>
		<# if ( typeof( data.selected_image.dataset.photoSetId ) !== 'undefined' && data.selected_image.dataset.photoSetId != '' ) { #>
		<div class="photo-set-info">
			<a href="#" class="button media-button button-primary sportspost-photo-set-photos" data-photo-set-id="{{ data.selected_image.dataset.photoSetId }}">Show photos in this set</a>
		</div>
		<# } else { #>
		<?php if ( ! apply_filters( 'disable_captions', '' ) ) : ?>
			<label class="setting caption">
				<span><?php _e('Caption', 'sportspost'); ?></span>
				<textarea id="caption-sportspost" data-setting="caption">{{{ data.selected_image.alt }}}</textarea>
			</label>
		<?php endif; ?>
		<label class="setting alt-text">
			<span><?php _e('Title', 'sportspost'); ?></span>
			<input id="title-sportspost" type="text" data-setting="title" value="{{{ data.selected_image.title }}}" />
			<input name="original-title" type="hidden" value="{{{ data.selected_image.title }}}" />
		</label>
		<label class="setting alt-text">
			<span><?php _e('Alt Text', 'sportspost'); ?></span>
			<input id="alt-sportspost" type="text" data-setting="alt" value="{{{ data.selected_image.title }}}" />
		</label>
		<div class="setting align">
			<span><?php _e('Align', 'sportspost'); ?></span>
			<select class="alignment" data-setting="align" name="sportspost-align">
				<option value="left"> <?php esc_attr_e('Left'); ?> </option>
				<option value="center"> <?php esc_attr_e('Center'); ?> </option>
				<option value="right"> <?php esc_attr_e('Right'); ?> </option>
				<option selected="selected" value="none"> <?php esc_attr_e('None'); ?> </option>
			</select>
		</div>
		<div class="setting link-to">
			<span><?php _e('Link To', 'sportspost'); ?></span>
			<select class="link-to" data-setting="link-to" name="sportspost-link">
				<option value="file"> <?php esc_attr_e('Media File'); ?> </option>
				<option value="post"> <?php esc_attr_e('Attachment Page'); ?> </option>
				<option value="custom"> <?php esc_attr_e('Custom URL'); ?> </option>
				<option selected="selected" value="none"> <?php esc_attr_e('None'); ?> </option>
			</select>
			<input type="text" class="link-to-custom hidden" data-setting="link-to-custom" name="sportspost-link-to-custom" />
		</div>
		<# } #>
		<?php do_action('sportspost_settings_after'); ?>
    </script>
