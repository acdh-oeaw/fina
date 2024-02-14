<?php

class Kma {

	public static function initExtension( $credits = [] ) {
	}

	public static function parserFunctioNoparse( Parser $parser, ...$argv ) {
		return [ 'text' => $argv[0], 'noparse' => true, 'isHTML' => true ];
	}

}
