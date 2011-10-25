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
class tx_contentreplacer_service_SpanParser extends tx_contentreplacer_service_AbstractParser {
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
	 * Each term has some additional properties:
	 * - pre: attributes before the class attribute
	 * - post: attributes after the class attribute
	 * - classAttribute: the class attribute without the replacement class
	 *
	 * @param string $content
	 * @return array
	 */
	public function parse($content) {
			// fetch terms
		$matches = array();
		$prefix = preg_quote($this->extensionConfiguration['prefix'], '/');
		$pattern = '/' .
			'<span' . // This expression includes any span nodes and parses
				'(?=[^>]+' . // any attributes of the beginning start tag.
					'(?=(class="([^"]*?' . $prefix . '[^"]+?)"))' .
				')' . // Use only spans which start with the defined class prefix
			' (.*?)\1(.*?)>' . // and stop if the closing character is reached.
			'(.*?)<\/span>' . // Finally we fetch the span content!
			'/is';
		preg_match_all($pattern, $content, $matches);

			// order terms by category
		$categories = array();
		foreach ($matches[5] as $index => $term) {
			$term = trim($term);

				// select the css class with the category (defined by the prefix)
			$category = '';
			$classes = explode(' ', $matches[2][$index]);
			foreach ($classes as $classIndex => $class) {
				$class = trim($class);

					// empty prefix === no category === no replacement
				if (FALSE !== strpos($class, $this->extensionConfiguration['prefix'])) {
					$category = str_replace($this->extensionConfiguration['prefix'], '', $class);
					unset($classes[$classIndex]);
					break;
				}
			}

				// error prevention (should never happen)
			if ($category === '') {
				t3lib_div::sysLog(
					'Incorrect match: ' . $classes,
					'content_replacer',
					t3lib_div::SYSLOG_SEVERITY_WARNING
				);

				continue;
			}

				// add the category/term with some additional information's
			$categories[$category][$term]['pre'] = $matches[3][$index];
			$categories[$category][$term]['post'] = $matches[4][$index];

			$categories[$category][$term]['classAttribute'] = '';
			$otherClasses = implode(' ', $classes);
			if ($otherClasses !== '') {
				$categories[$category][$term]['classAttribute'] = 'class="' . $otherClasses . '"';
			}
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
		foreach ($terms as $termName => $term) {
				// term has no replacement information's -> use default replacement or an empty string
			if (!isset($term['uid'])) {
				$term = array_merge((array) $term, $defaultReplacement);
				$term['term'] = $termName;
			}

				// built regular expression for this term
			$searchClass = preg_quote($this->extensionConfiguration['prefix'] . $category, '/');
			$search[$termName] = '/' .
				'<span ' . preg_quote($term['pre'], '/') .
					'class="([^"]*?)' . $searchClass . '([^"]*?)"' .
					preg_quote($term['post'], '/') . '>' .
					'\s*?' . preg_quote($term['term'], '/') . '\s*?' .
				'<\/span>' .
				'/i';

				// built replacement text for this term
			$replace[$termName] = $this->prepareReplacementTerm(
				$term['replacement'],
				trim($term['stdWrap']),
				$termName
			);

			if (trim($term['pre']) !== '' || trim($term['post']) !== '' || trim($term['classAttribute']) !== '') {
				$attributes = trim($term['pre'] . ' ' . $term['post'] . ' ' . $term['classAttribute']);
				$replace[$termName] = '<span ' . $attributes . '>' . $replace[$termName] . '</span>';
			}
		}

			// replace all terms by multiple regular expressions
		return preg_replace($search, $replace, $content);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Service/class.tx_contentreplacer_service_SpanParser.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Service/class.tx_contentreplacer_service_SpanParser.php']);
}

?>