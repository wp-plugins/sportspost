<?php
/**
 * The template for the Icon Sportswire user connect dialog.
 *
 * @since   2.0.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin/partials
 */
?>
	<script type="text/html" id="tmpl-sportspost-photos-user-connect">
		<form id="sportspost-user-connect-form" autocomplete="off">
		<label class="setting username">
			<span><?php _e( 'Username', 'sportspost'); ?></span>
			<input id="iconsportswire_username" type="text" data-setting="iconsportswire_username" value="" />
		</label>
		<label class="setting password">
			<span><?php _e( 'Password', 'sportspost'); ?></span>
			<input id="iconsportswire_password" type="password" data-setting="iconsportswire_password" value="" />
		</label>
		</form>
    </script>
