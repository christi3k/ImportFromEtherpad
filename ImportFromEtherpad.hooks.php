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
 * @file
 * @ingroup Extensions
 */

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
