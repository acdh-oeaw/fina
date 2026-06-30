<?php

class KmaHooks {

	/**
	 * Register any render callbacks with the parser
	 *
	 * @param Parser $parser
	 */
	public static function onParserFirstCallInit( Parser $parser ) {
		$parser->setFunctionHook( 'noparse', [ Kma::class, 'parserFunctioNoparse' ] );
	}

	/**
	 * @param OutputPage $outputPage
	 * @param ParserOutput $parserOutput
	 */
	public static function onOutputPageParserOutput( OutputPage $outputPage, ParserOutput $parserOutput ) {

	}

}
