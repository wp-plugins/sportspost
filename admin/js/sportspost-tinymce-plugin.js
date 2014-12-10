/* global tinymce, sportsPostPlayerLinkL10n */
tinymce.PluginManager.add( 'sportspost', function( editor ) { 

	editor.addCommand( 'Player_Link', function() {
		if ( typeof window.sportsPostPlayerLink !== 'undefined' ) {
			window.sportsPostPlayerLink.open( editor.id );
		}
	});
	
	editor.addShortcut( 'alt+shift+s', '', 'Player_Link' );

	editor.addButton( 'sportspost-playerlink', {
		icon: 'playerlink',
		image: sportsPostPlayerLinkL10n.iconURL,
		tooltip: sportsPostPlayerLinkL10n.title,
		shortcut: 'Alt+Shift+S',
		cmd: 'Player_Link',
	});

	editor.addMenuItem( 'link', {
		icon: 'playerlink',
		text: sportsPostPlayerLinkL10n.title,
		shortcut: 'Alt+Shift+S',
		cmd: 'Player_Link',
		stateSelector: 'a[href]',
		context: 'insert',
		prependToContext: true
	});
	
});

