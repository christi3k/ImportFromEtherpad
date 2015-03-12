<?php

class EtherpadToPage 
{
	public static function setHook( Parser $parser )
	{
		$parser->setHook('etherpad', __CLASS__ . '::parseTag');
	}

	public static function parseTag( $input, array $args, Parser $parser, PPFrame $frame )
	{
		return 'your pad: ' . htmlentities( $input );

	}
}
/* vim:set ts=4 sw=4 sts=4 noexpandtab: */
