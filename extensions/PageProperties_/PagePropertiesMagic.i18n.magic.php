<?php

/**
 * This file is part of the MediaWiki extension PageProperties.
 *
 * PageProperties is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * PageProperties is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PageProperties.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup extensions
 * @author thomas-topway-it <thomas.topway.it@mail.com>
 * @copyright Copyright ©2021-2022, https://wikisphere.org
 */

$magicWords = [];

// see here
// https://www.mediawiki.org/wiki/Manual:Magic_words
// '0' stands for 'case insensitive'

$magicWords['en'] = [

'background image' =>  [ 0, 'background image' ],
        'background_image' =>  [ 0, 'background_image' ],

	'noparse isHTML' => [ 0, 'noparse isHTML' ],
	'noparse_isHTML' => [ 0, 'noparse_isHTML' ],

];
