<?php
if ( function_exists( 'wfLoadExtension' ) ) {
	wfLoadExtension( 'Webhooks' );
	wfWarn(
		'Deprecated PHP entry point used for Webhooks extension. ' .
		'Please use wfLoadExtension instead, ' .
		'see https://www.mediawiki.org/wiki/Extension_registration for more details.'
	);
	return;
} else {
	die( 'This version of the Webhooks extension requires MediaWiki 1.25+' );
}