<?php

$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'EtherpadToPage',
	'description' => 'Create a wiki page from an etherpad.',
	'version' => '1.0',
	'author' => '[http://christiekoehler.com Christie Koehler]',
	'url' => 'https://github.com/christi3k/EtherpadToPage',
	'license-name' => 'MPL 2.0'
);

$includesDirectory = __DIR__ . '/includes';

$wgAutoloadClasses['SpecialEtherpadToPage'] = __DIR__ . '/SpecialEtherpadToPage.php';
$wgMessagesDirs['EtherpadToPage'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['EtherpadToPageAlias'] = __DIR__ . '/EtherpadToPage.alias.php';
$wgSpecialPages['EtherpadToPage'] = 'SpecialEtherpadToPage';


/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
