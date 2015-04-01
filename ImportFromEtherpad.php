<?php
/**
 * Extension that adds ability to create wiki pages from etherpads.
 * See https://github.com/christi3k/ImportFromEtherpad for more information.
 * Copyright (C) 2015 Christie Koehler <ck@christi3k.net>
 *
 * @section LICENSE
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @section Configuration
 * The following settings may be overwritten in LocalSettings.php.
 * Local configuration must be done after including this extension using
 * require("extensions/ImportFromEtherpad.php");
 * - $wgImportFromEtherpadSettings->pathToPandoc
 * - $wgImportFromEtherpadSettings->pandocCmd
 * - $wgImportFromEtherpadSettings->contentRegexes
 * - $wgImportFromEtherpadSettings->hostRegexes
 * - $wgImportFromEtherpadSettings->pathRegexes
 *
 * @file
 * @ingroup Extensions
 */

// Must be run inside Mediawiki environment
if ( !defined( 'MEDIAWIKI' ) ) die();

define( 'ImportFromEtherpad_VERSION', '1.0.0' );

class ImportFromEtherpadSettings {
	/**
	 * system path to pandoc executable
	 * Non-Windows default: /usr/bin
	 * Windows default: C:/Program Files/Pandoc/
	 *
	 * @var string $pathToPandoc
	 */
	public $pathToPandoc;

	/** 
	 * system command for pandoc
	 * Non-Windows default: pandoc
	 * Windows default: pandoc.exe
	 *
	 * @var string $pandocCmd
	 */
	public $pandocCmd;

	/**
	 * multi-dimensional array of regexes used to clean up converted content
	 *
	 * Should use the form array('find regex','replacement string'). Regex pattern
	 * will automatically be placed inside delimiters, so indicate patterns without
	 * preceding and trailing slashes.
	 *
	 * Example: 
	 * $wgImportFromEtherpadSettings->contentRegexs[] = array("<br\s*\/>","\n")
	 *
	 * @var array $contentRegexes
	 */
	public $contentRegexs;

	/**
	 * multi-dimensional array of regexes used to determine suggested target title
	 *
	 * Should use the form array('find regex','replacement string'). Regex pattern
	 * will automatically be placed inside delimiters, so indicate patterns without
	 * preceding and trailing slashes.
	 *
	 * Example: 
	 * $wgImportFromEtherpadSettings->hostRegexs[] = array('\w+\.\w+\.\w+','');
	 *
	 * @var array $contentRegexes
	 */
	public $hostRegexs;

	/**
	 * multi-dimensional array of regexes used to determine suggested target title
	 *
	 * Should use the form array('find regex','replacement string'). Regex pattern
	 * will automatically be placed inside delimiters, so indicate patterns without
	 * preceding and trailing slashes.
	 *
	 * Example: 
	 * $wgImportFromEtherpadSettings->pathRegexs[] = array('\w+\.\w+\.\w+','');
	 *
	 * @var array $contentRegexes
	 */
	public $pathRegexs;
}

$GLOBALS['wgImportFromEtherpadSettings'] = new ImportFromEtherpadSettings();

//self executing anonymous function to prevent global scope assumptions
//(borrowed from GraphViz extension)
call_user_func( function() {

	// Set pandoc execution path
	if ( stristr( PHP_OS, 'WIN' ) && !stristr( PHP_OS, 'Darwin' ) ) {
		$GLOBALS['wgImportFromEtherpadSettings']->pathToPandoc = 'C:/Program Files/Pandoc/';
		$GLOBALS['wgImportFromEtherpadSettings']->pandocCmd = 'pandoc.exe';
	} else {
		$GLOBALS['wgImportFromEtherpadSettings']->pathToPandoc = '/usr/bin/';
		$GLOBALS['wgImportFromEtherpadSettings']->pandocCmd = 'pandoc';
	}

	// set tidy regex that most folks will want:
	$GLOBALS['wgImportFromEtherpadSettings']->contentRegexs[] = array("<br\s*\/>","\n");
	// remove non-breaking spaces
	// (for whatever reason, these sneak into etherpads when they shouldn't
	$GLOBALS['wgImportFromEtherpadSettings']->contentRegexs[] = array("\x{00a0}+","");

	// remove url
	$GLOBALS['wgImportFromEtherpadSettings']->hostRegexs[] = array('\w+\.\w+\.\w+','');

	// remove leading p/, common to etherpad lite 
	$GLOBALS['wgImportFromEtherpadSettings']->pathRegexs[] = array('^p/','');

	// remove hyphens because they make bad wiki titles
	$GLOBALS['wgImportFromEtherpadSettings']->pathRegexs[] = array('-',' ');

	$dir = __DIR__;

	$GLOBALS['wgExtensionCredits']['specialpage'][] = array(
		'path' => __FILE__,
		'name' => 'Import From Etherpad',
		'description' => 'Create a wiki page from an etherpad.',
		'version' => '1.0.0',
		'author' => '[http://christiekoehler.com Christie Koehler]',
		'url' => 'https://github.com/christi3k/ImportFromEtherpad',
		'license-name' => 'GPL 3.0+'
	);

	$GLOBALS['wgAutoloadClasses']['SpecialImportFromEtherpad'] = $dir . '/SpecialImportFromEtherpad.php';
	$GLOBALS['wgAutoloadClasses']['ImportFromEtherpadHooks'] = $dir . '/ImportFromEtherpad.hooks.php';
	$GLOBALS['wgMessagesDirs']['ImportFromEtherpad'] = $dir . '/i18n';
	$GLOBALS['wgExtensionMessagesFiles']['ImportFromEtherpadAlias'] = $dir . '/ImportFromEtherpad.alias.php';
	$GLOBALS['wgSpecialPages']['ImportFromEtherpad'] = 'SpecialImportFromEtherpad';

	// register two javascript files that enable target title suggest
	$GLOBALS['wgResourceModules']['ext.ImportFromEtherpad.main'] = array(
		'scripts' => array(
			'modules/ext.ImportFromEtherpad.main.js',
		),
		'messages' => array(),
		'dependencies' => array(),
		'localBasePath' => __DIR__,
		'remoteExtPath' => $dir,
	);

	$GLOBALS['wgResourceModules']['ext.ImportFromEtherpad.main.init'] = array(
		'scripts' => array(
			'modules/ext.ImportFromEtherpad.main.init.js',
		),
		'messages' => array(),
		'dependencies' => array('ext.ImportFromEtherpad.main'),
		'localBasePath' => __DIR__,
		'remoteExtPath' => $dir,
	);

	// register hook that will load javascript modules defined above
	$GLOBALS['wgHooks']['BeforePageDisplay'][] = 'ImportFromEtherpadHooks::onBeforePageDisplay';

	// register hoook that will load the confg vars that javascript needs
	$GLOBALS['wgHooks']['ResourceLoaderGetConfigVars'][] = 'ImportFromEtherpadHooks::onResourceLoaderGetConfigVars';

	$GLOBALS['wgHooks']['BaseTemplateToolbox'][] = 'ImportFromEtherpadHooks::addToolboxItem';
} );

/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
