<?php
/**
 * The template for a single image in the media dialog.
 *
 * @since   2.0.0
 *
 * @package    SportsPost
 * @subpackage SportsPost/admin/partials
 */
?>
	<script type="text/html" id="tmpl-sportspostimage">
		<# if ( typeof( data.photo_set_id ) !== 'undefined' && data.photo_set_id != '' ) { #>
		<img id="{{ data.id }}" class="folder" src="{{ data.thumbnail }}" alt="{{{ data.caption }}}" title="View photo set" data-full="{{ data.full }}" data-caption="{{ data.caption }}" data-photo-set-id="{{ data.photo_set_id }}" data-photo-set-name="{{ data.photo_set_name }}" data-link="/{{ data.league_link }}/{{ data.photo_set_id }}" />
		<# if ( typeof(data.num_photos) !== 'undefined' ) { #>
			<p class="num_photos">{{{ data.num_photos }}} Photos</p>
		<# } #>
		<# if ( typeof(data.short_caption) !== 'undefined' ) { #>
			<p>{{{ data.short_caption }}}</p>
		<# } #>
		<a class="check" id="check-link-{{ data.id }}" href="#" title="Deselect"><div id="check-{{ data.id }}" class="media-modal-icon"></div></a>
		<# } else { #>
		<img id="{{ data.id }}" class="<# if ( typeof(data.folder) !== 'undefined' ) { #>folder<# } else { #>image<# } #>" src="{{ data.thumbnail }}" alt="{{{ data.caption }}}" title="{{{ data.title }}}" data-full="{{ data.full }}" data-caption="{{ data.caption }}" />
		<# if ( typeof(data.short_caption) !== 'undefined' ) { #>
			<p>{{{ data.short_caption }}}</p>
		<# } #>
		<a class="check" id="check-link-{{ data.id }}" href="#" title="Deselect"><div id="check-{{ data.id }}" class="media-modal-icon"></div></a>
		<# } #>
    </script>
