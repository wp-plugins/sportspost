/* global tinyMCEPreInit */

( function( $ ) {

	function sportspost_editor_setup( ed ) {
		ed.on( 'init', function() {
			setTimeout( function() {
				$( document ).trigger( 'content_editor_ready' );
			}, 2000 );
		});
	}

	var id;
	for ( id in tinyMCEPreInit.mceInit ) {
		if ( id == 'content' ) {
			tinyMCEPreInit.mceInit[ id ].setup = sportspost_editor_setup;
		}
	}

}( jQuery ));
