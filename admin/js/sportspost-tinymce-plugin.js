/* global tinymce, sportspost_data */
tinymce.PluginManager.add( 'sportspost', function( editor ) { 

	editor.addCommand( 'Player_Link', function() {
		if ( typeof window.sportsPostPlayerLink !== 'undefined' ) {
			window.sportsPostPlayerLink.open( editor.id );
		}
	});
	
	editor.addShortcut( 'alt+shift+s', '', 'Player_Link' );

	editor.addButton( 'sportspost-playerlink', {
		icon: 'playerlink',
		image: sportspost_data.icon_url,
		tooltip: sportspost_data.title,
		shortcut: 'Alt+Shift+S',
		cmd: 'Player_Link',
	});

	editor.addMenuItem( 'link', {
		icon: 'playerlink',
		text: sportspost_data.title,
		shortcut: 'Alt+Shift+S',
		cmd: 'Player_Link',
		stateSelector: 'a[href]',
		context: 'insert',
		prependToContext: true
	});
	
});

