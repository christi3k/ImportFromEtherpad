<?php

// Must be run inside Mediawiki environment
if ( !defined( 'MEDIAWIKI' ) ) die();

define( 'ImportFromEtherpad_VERSION', '1.0' );

class ImportFromEtherpadSettings {
	public $pathToPandoc;
	public $pandocCmd;
	public $contentRegexs;
}

$GLOBALS['wgImportFromEtherpadSettings'] = new ImportFromEtherpadSettings();

//self executing anonymous function to prevent global scope assumptions
//(borrowed from GraphViz extension)
call_user_func( function() {

	// Set execution path
	if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'Darwin' ) ) {
		$GLOBALS['wgImportFromEtherpadSettings']->pathToPandoc = 'C:/Program Files/Pandoc/';
		$GLOBALS['wgImportFromEtherpadSettings']->pandocCmd = 'pandoc.exe';
	} else {
		$GLOBALS['wgImportFromEtherpadSettings']->pathToPandoc = '/usr/bin/';
		$GLOBALS['wgImportFromEtherpadSettings']->pandocCmd = 'pandoc';
	}

	// set tidy regex that most folks will want:
	$GLOBALS['wgImportFromEtherpadSettings']->contentRegexs[] = array("<br\s*\/>","\n");

	$dir = __DIR__;

	$GLOBALS['wgExtensionCredits']['specialpage'][] = array(
		'path' => __FILE__,
		'name' => 'Import From Etherpad',
		'description' => 'Create a wiki page from an etherpad.',
		'version' => '1.0',
		'author' => '[http://christiekoehler.com Christie Koehler]',
		'url' => 'https://github.com/christi3k/ImportFromEtherpad',
		'license-name' => 'MPL 2.0'
	);

	$GLOBALS['wgAutoloadClasses']['SpecialImportFromEtherpad'] = $dir . '/SpecialImportFromEtherpad.php';
	$GLOBALS['wgMessagesDirs']['ImportFromEtherpad'] = $dir . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['ImportFromEtherpadAlias'] = $dir . '/ImportFromEtherpad.alias.php';
	$GLOBALS['wgSpecialPages']['ImportFromEtherpad'] = 'SpecialImportFromEtherpad';

} );



/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
