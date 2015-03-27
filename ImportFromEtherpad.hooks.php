<?php

class ImportFromEtherpadHooks {
	public static function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModules( 'ext.ImportFromEtherpad.main.init' );
	}

	/**
	 * Expose configuration variables through mw.config in javascript.
	 */
	public static function onResourceLoaderGetConfigVars( &$vars ) {
		global $wgImportFromEtherpadSettings;

		$vars['wgImportFromEtherpadSettings'] = $wgImportFromEtherpadSettings;

		return true;
	}
}
/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
