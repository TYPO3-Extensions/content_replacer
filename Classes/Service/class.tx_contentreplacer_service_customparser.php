<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Stefan Galinski <stefan.galinski@gmail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Substitution service that parses and replaces special span tags inside the code
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 * @package TYPO3
 * @subpackage content_replacer
 */
class tx_contentreplacer_service_CustomParser extends tx_contentreplacer_service_AbstractParser {
	/**
	 * @var string
	 */
	protected $wrapCharacter = '';

	/**
	 * Sets the wrap character
	 *
	 * @param string $wrapCharacter
	 * @return void
	 */
	public function setWrapCharacter($wrapCharacter) {
		$this->wrapCharacter = $wrapCharacter;
	}

	/**
	 * This function parses the generated content from TYPO3 and returns an ordered list
	 * of terms with their related categories.
	 *
	 * Structure:
	 *
	 * category1
	 * |-> term1
	 * |-> term2
	 * category2
	 * |-> term1
	 * ...
	 *
	 * @param string $content
	 * @return array
	 */
	public function parse($content) {
		$matches = array();
		$prefix = preg_quote($this->extensionConfiguration['prefix'], '/');
		$char = preg_quote($this->wrapCharacter, '/');
		$pattern = '/' . $char . $prefix . '([^' . $char . ']+?)' .
			$char . '(.+?)' . $char . $char . '/is';
		preg_match_all($pattern, $content, $matches);

		$categories = array();
		foreach ($matches[2] as $index => $term) {
			$categories[trim($matches[1][$index])][trim($term)] = array();
		}

		return $categories;
	}

	/**
	 * Replaces the given terms with their related replacement values.
	 *
	 * @param string $category
	 * @param array $terms
	 * @param string $content
	 * @return string
	 */
	public function replaceByCategory($category, array $terms, $content) {
		$search = $replace = array();
		$defaultReplacement = $this->prepareFoundTerms($terms, $category);
		$char = preg_quote($this->wrapCharacter, '/');
		foreach ($terms as $termName => $term) {
			if (!isset($term['uid'])) {
				$term = array_merge((array) $term, $defaultReplacement);
				$term['term'] = $termName;
			}

			$searchClass = preg_quote($this->extensionConfiguration['prefix'] . $category, '/');
			$search[$termName] = '/' . $char . $searchClass . $char .
				'\s*?' . preg_quote($term['term'], '/') . '\s*?' . $char . $char . '/i';

			$replace[$termName] = $this->prepareReplacementTerm(
				$term['replacement'],
				trim($term['stdWrap']),
				$termName
			);
		}

		return preg_replace($search, $replace, $content);
	}
}

if (defined(
		'TYPO3_MODE'
	) && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Service/class.tx_contentreplacer_service_SpanParser.php']
) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Service/class.tx_contentreplacer_service_SpanParser.php']);
}

?>