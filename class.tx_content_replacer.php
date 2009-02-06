<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009 Stefan Galinski <stefan.galinski@gmail.com>
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
 * This file contains the complete logic of the extension.
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 */

/**
 * This class contains the parsing and replacing mechanisms of the extension.
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 */
class tx_content_replacer {
	/** @var $extConfig array holds the extension configuration */
	var $extConfig = array();

	/** @var $parseFunc array holds the lib.parseFunc_RTE configuration */
	var $parseFunc = array();

	/**
	 * Constructor
	 *
	 * Prepares the extension configuration array!
	 *
	 * @return void
	 */
	function __construct() {
		// global extension configuration
		$this->extConfig = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer']
		);

		// typoscript extension configuration
		$tsConfig = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_content_replacer.'];
		if (is_array($tsConfig)) {
			foreach ($tsConfig as $key => $value) {
				$this->extConfig[$key] = $value;
			}
		}

		// get the parseFunc_RTE configuration and add the removeWrapping property to remove
		// the surrounding p tags
		$this->parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
		$this->parseFunc['nonTypoTagStdWrap.']['encapsLines.']['removeWrapping'] = true;
	}

	/**
	 * Contains the process logic of the whole extension!
	 *
	 * @return bool
	 */
	function main() {
		// do nothing if the disable flag is set
		if ($this->extConfig['disable']) {
			return true;
		}

		// the content should be parsed until all occurences are replaced
		// this enables the replacing of occurences in the replacement texts
		$loopCounter = 0;
		while (true) {
			// get categories
			$categories = $this->parseContent();

			// cancel condition of the endless loop
			if (!count($categories) || ++$loopCounter > $this->extConfig['amountOfPasses']) {
				break;
			}

			// loop categories
			foreach ($categories as $category => $terms) {
				// fetch term informations (the wildcard term "*" is added manually to the array)
				$terms[] = '*';
				$terms = $this->fetchTerms($terms, $category);
				if (!count($terms)) {
					continue;
				}

				// loop terms
				$search = $replace = array();
				foreach($terms as $index => $term) {
					// use an high index for the wildcard term to be applied at the end of
					// the replacing process
					$index = ($term['term'] == '*' ? 999 : $index);

					// built search string (respects the wildcard * for any term)
					$search[$index] =
						'/\<span class="' . $this->extConfig['prefix'] . $category . '"\>\s*?' .
						($term['term'] == '*' ? '.*?' : $term['term']) . '\s*?\<\/span\>/i';

					// prepare replacement string
					$replace[$index] = $this->prepareTermReplacement(
						$term['replacement'],
						$term['stdWrap']
					);
				}

				// the arrays needs to be reordered by the array keys for the wildcard term
				ksort($search);
				ksort($replace);

				// finally replace the occurences for the category
				$GLOBALS['TSFE']->content = preg_replace($search, $replace, $GLOBALS['TSFE']->content);
			}
		}

		return true;
	}

	/**
	 * This function parses the generated content by TYPO3 and returns an ordered list
	 * of found terms with there related categories. The structure is like the following example:
	 *
	 * - category1
	 *   - term1
	 *   - term2
	 * - category2
	 *   - term1
	 *
	 * @return array ordered list of found matches by the category
	 */
	function parseContent() {
		// parse span tags
		$matches = array();
		$pattern = '/\<span class="' . $this->extConfig['prefix'] . '(.+?)"\>(.*?)\<\/span\>/i';
		preg_match_all($pattern, $GLOBALS['TSFE']->content, $matches);

		// order found terms by category
		$categories = array();
		foreach ($matches[2] as $index => $term) {
			$categories[$matches[1][$index]][] = $term;
		}

		return $categories;
	}

	/**
	 * This function returns the given term names with their related informations.
	 *
	 * @param $terms array list of term names
	 * @param $category string category name
	 * @return array terms with their related informations
	 */
	function fetchTerms($terms, $category) {
		// escape strings
		$category = $GLOBALS['TYPO3_DB']->fullQuoteStr(
			$category,
			'tx_content_replacer_category'
		);

		$termsWhereClause = array();
		foreach ($terms as $term) {
			$termsWhereClause[] = $GLOBALS['TYPO3_DB']->fullQuoteStr(trim($term), 'tx_content_replacer_term');
		}

		// get replace terms
		$GLOBALS['TYPO3_DB']->debugOutput = false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_content_replacer_term.uid, tx_content_replacer_term.pid, ' .
				'term, replacement, stdWrap, category_uid',
			'tx_content_replacer_term, tx_content_replacer_category',
			'term IN (' . implode(', ', $termsWhereClause) . ') AND ' .
				'sys_language_uid IN (-1, 0) AND category = ' . $category . ' AND ' .
				'tx_content_replacer_category.uid = category_uid ' .
				$GLOBALS['TSFE']->cObj->enableFields('tx_content_replacer_term') . ' ' .
				$GLOBALS['TSFE']->cObj->enableFields('tx_content_replacer_category'),
			'', // GROUP BY
			'', // ORDER BY
			'' // LIMIT
		);

		// record overlay (enables multilanguage support)
		$terms = array();
		while ($term = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($GLOBALS['TSFE']->sys_language_content > 0) {
				$term = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
					'tx_content_replacer_term',
					$term,
					$GLOBALS['TSFE']->sys_language_content
				);
			}

			if (is_array($term)) {
				$terms[] = $term;
			}
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $terms;
	}

	/**
	 * This function returns a prepared text. The given text has ran trough the
	 * rte parse and stdWrap function. The latter one must be a stdWrap configuration class
	 * in the namespace of this extension (plugin.tx_content_replacer.).
	 *
	 * @param $replacement string text
	 * @param $stdWrap stdWrap configuration class (see description for more informations)
	 * @return string prepared text
	 */
	function prepareTermReplacement($replacement, $stdWrap) {
		// rte transformation of the replacement string
		$replacement = $GLOBALS['TSFE']->cObj->parseFunc($replacement, $this->parseFunc);

		// stdWrap execution if available
		if ($stdWrap !== '') {
			$replacement = $GLOBALS['TSFE']->cObj->stdWrap(
				$replacement,
				$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_content_replacer.'][$stdWrap . '.']
			);
		}

		return $replacement;
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/content_replacer/class.tx_content_replacer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/content_replacer/class.tx_content_replacer.php']);
}

?>
