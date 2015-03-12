<?php

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'EtherpadToPage',
	'description' => 'Create a wiki page from an etherpad.',
	'version' => '1.0',
	'author' => '[http://christiekoehler.com Christie Koehler]',
	'url' => 'https://github.com/christi3k/EtherpadToPage',
	'license-name' => 'MPL 2.0'
);

$includesDirectory = __DIR__ . '/includes';

$wgAutoloadClasses['EtherpadToPage'] = $includesDirectory . '/EtherpadToPage.php';

$wgHooks['ParserFirstCallInit'][] = 'EtherpadToPage::setHook';

/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
