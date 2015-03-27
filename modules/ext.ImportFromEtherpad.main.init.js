/**
 * Initialize main ImportFromEtherpad
 *
 * This file is part of the 'ext.ImportFromEtherpad.init' module,
 * which is enqueued for loading from ExampleHooks::onBeforePageDisplay()
 * in ImportFromEtherpad.hooks.php.
 */
( function ( mw, $ ) {

	// Let jQuery invoke the init method as soon as the document is ready
	// $(..) is short for $(document).ready(..).
	// See also api.jquery.com/jQuery and api.jquery.com/ready
	$( mw.libs.suggestTitle.init );

}( mediaWiki, jQuery ) );


