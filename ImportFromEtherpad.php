<?php

// Must be run inside Mediawiki environment
if ( !defined( 'MEDIAWIKI' ) ) die();

define( 'EtherpadToPage_VERSION', '1.0' );

class EtherpadToPageSettings {
	public $pathToPandoc;
}

$GLOBALS['wgEtherpadToPageSettings'] = new EtherpadToPageSettings();

//self executing anonymous function to prevent global scope assumptions
//(borrowed from GraphViz extension)
call_user_func( function() {

	// Set execution path
	if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'Darwin' ) ) {
		$GLOBALS['wgEtherpadToPageSettings']->pathToPandoc = 'C:/Program Files/Pandoc/';
		$GLOBALS['wgEtherpadToPageSettings']->pandocCmd = 'pandoc.exe';
	} else {
		$GLOBALS['wgEtherpadToPageSettings']->pathToPandoc = '/usr/bin/';
		$GLOBALS['wgEtherpadToPageSettings']->pandocCmd = 'pandoc';
	}

	$dir = __DIR__;

	$GLOBALS['wgExtensionCredits']['specialpage'][] = array(
		'path' => __FILE__,
		'name' => 'EtherpadToPage',
		'description' => 'Create a wiki page from an etherpad.',
		'version' => '1.0',
		'author' => '[http://christiekoehler.com Christie Koehler]',
		'url' => 'https://github.com/christi3k/EtherpadToPage',
		'license-name' => 'MPL 2.0'
	);

	$GLOBALS['wgAutoloadClasses']['SpecialEtherpadToPage'] = $dir . '/SpecialEtherpadToPage.php';
	$GLOBALS['wgMessagesDirs']['EtherpadToPage'] = $dir . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['EtherpadToPageAlias'] = $dir . '/EtherpadToPage.alias.php';
	$GLOBALS['wgSpecialPages']['EtherpadToPage'] = 'SpecialEtherpadToPage';

} );



/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
