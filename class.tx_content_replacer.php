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
	private $extConfig = array();

	/** @var $parseFunc array holds the lib.parseFunc_RTE configuration */
	private $parseFunc = array();

	/**
	 * Constructor
	 *
	 * Prepares the extension configuration array!
	 *
	 * @return void
	 */
	public function __construct() {
		// global extension configuration
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer'])) {
			$this->extConfig = unserialize(
				$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer']
			);
		}

		// typoscript extension configuration
		$tsConfig = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_content_replacer.'];
		if (is_array($tsConfig)) {
			foreach ($tsConfig as $key => $value) {
				$this->extConfig[$key] = $value;
			}
		}

		// get the parseFunc_RTE configuration
		$this->parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
	}

	/**
	 * Just a wrapper for the main function! It's used for the contentPostProc-output hook.
	 * 
	 * @return bool
	 */
	public function contentPostProcOutput() {
		// only enter this hook if the page shouldn't be cached or has COA_INT
		// or USER_INT objects
		if (!$GLOBALS['TSFE']->no_cache
			&& !$GLOBALS['TSFE']->isINTincScript()
		) {
			return true;
		}

		// do nothing if the disable flag for the extension is set
		if ($this->extConfig['disable']) {
			return true;
		}

		return $this->main();
	}

	/**
	 * Just a wrapper for the main function!  It's used for the contentPostProc-cache hook.
	 *
	 * @return bool
	 */
	public function contentPostProcCached() {
		// only enter this hook if the page should be cached and hasn't any COA_INT
		// or USER_INT objects
		if ($GLOBALS['TSFE']->no_cache
			|| $GLOBALS['TSFE']->isINTincScript()
		) {
			return true;
		}

		// do nothing if the disable flag is set
		if ($this->extConfig['disable']) {
			return true;
		}

		return $this->main();
	}

	/**
	 * Contains the process logic of the whole extension!
	 *
	 * @return bool
	 */
	public function main() {
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
			foreach ($categories as $category => $foundTerms) {
				// fetch term informations (the wildcard term "*" is added manually to the array)
				$filterTerms = array_keys($foundTerms);
				$filterTerms[] = '*';
				$terms = $this->fetchTerms($filterTerms, $category);

				// merge entries which are on the page with the database informations
				$terms = array_merge(array_flip($filterTerms), $terms);

				// get default replacement if available
				$defaultReplacement = '';
				if (is_array($terms['*'])) {
					$defaultReplacement = $terms['*'];
				}
				unset($terms['*']);

				// loop terms
				$search = $replace = array();
				foreach($terms as $termName => $term) {
					// use default replacement if the term wasn't defined in the database
					if (!is_array($term)) {
						$term = $defaultReplacement;
						$term['term'] = $termName;
					}

					// built search string (respects the wildcard * for any term)
					$searchTerm = preg_quote(
						($term['term'] == '*' ? '.*?' : $term['term']),
						'/'
					);

					$searchClass = preg_quote(
						$this->extConfig['prefix'] . $category,
						'/'
					);

					$search[$termName] = '/' .
						'<span '. preg_quote($foundTerms[$termName]['pre'], '/') .
						'class="([^"]*?)' . $searchClass . '([^"]*?)"' .
						preg_quote($foundTerms[$termName]['post'], '/') . '>' .
						'\s*?' . $searchTerm . '\s*?' .
						'<\/span>'.
					'/i';

					// prepare replacement string
					$replace[$termName] = $this->prepareTermReplacement(
						$term['replacement'],
						$term['stdWrap'],
						$termName
					);

					// pre or post assignments in the origin span tag?
					if (trim($foundTerms[$termName]['pre']) != '' ||
						trim($foundTerms[$termName]['post']) != '' ||
						trim($foundTerms[$termName]['classAttribute'])
					) {
						$attributes = trim(
							$foundTerms[$termName]['pre'] . ' ' .
							$foundTerms[$termName]['post'] . ' ' .
							$foundTerms[$termName]['classAttribute']
						);

						$replace[$termName] = '<span ' . $attributes . '>' .
							$replace[$termName] . '</span>';
					}
				}

				// finally replace the occurences for the category
				$GLOBALS['TSFE']->content = preg_replace(
					$search,
					$replace,
					$GLOBALS['TSFE']->content
				);
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
	protected function parseContent() {
		// parse span tags
		$matches = array();
		$prefix = preg_quote($this->extConfig['prefix'], '/');
		$pattern = '/' .
			'<span' . // This expression includes any span nodes and parses
				'(?=[^>]+' . // any attributes of the beginning start tag.
					// Use only spans which starts with the defined prefix in the class attribute
					'(?=(class="([^"]*?' . $prefix . '[^"]+?)"))' .
				')' .
			' (.*?)\1(.*?)>' . // and stop if the closing character is reached.
			'(.*?)<\/span>' . // Finally we fetch the span content!
			'/is';
		preg_match_all($pattern, $GLOBALS['TSFE']->content, $matches);

		// order found terms by category
		$categories = array();
		foreach ($matches[5] as $index => $term) {
			$term = trim($term);

			// fetch the category from the available classes
			$classes = explode(' ', $matches[2][$index]);
			$category = '';
			foreach ($classes as $index => $class) {
				if (strpos(trim($class), $this->extConfig['prefix']) !== false) {
					$category = str_replace($this->extConfig['prefix'], '', $class);
					unset($classes[$index]);
					break;
				}
			}

			// something strange happened...
			if ($category == '') {
				continue;
			}

			$categories[$category][$term]['pre'] = $matches[3][$index];
			$categories[$category][$term]['post'] = $matches[4][$index];

			// add the additional classes
			$categories[$category][$term]['classAttribute'] = '';
			$otherClasses = implode(' ', $classes);
			if ($otherClasses != '') {
				$categories[$category][$term]['classAttribute'] = 'class="' . $otherClasses . '"';
			}
		}

		return $categories;
	}

	/**
	 * This function returns the given term names with their related informations.
	 *
	 * @param $filterTerms array list of term names
	 * @param $category string category name
	 * @return array terms with their related informations
	 */
	protected function fetchTerms($filterTerms, $category) {
		// escape strings
		$category = $GLOBALS['TYPO3_DB']->fullQuoteStr(
			$category,
			'tx_content_replacer_category'
		);

		$termsWhereClause = array();
		foreach ($filterTerms as $term) {
			$termsWhereClause[] = $GLOBALS['TYPO3_DB']->fullQuoteStr(
				trim($term),
				'tx_content_replacer_term'
			);
		}

		// get replace terms
		$GLOBALS['TYPO3_DB']->debugOutput = false;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
			'tx_content_replacer_term.uid, tx_content_replacer_term.pid, ' .
				'term, replacement, stdWrap, category_uid, sys_language_uid',
			'tx_content_replacer_term, tx_content_replacer_category',
			'term IN (' . implode(', ', $termsWhereClause) . ') AND ' .
				'sys_language_uid IN (-1, 0) AND category = ' . $category . ' AND ' .
				'tx_content_replacer_category.uid = category_uid ' .
				$GLOBALS['TSFE']->cObj->enableFields('tx_content_replacer_term') . ' ' .
				$GLOBALS['TSFE']->cObj->enableFields('tx_content_replacer_category')
		);

		// define language mode
		$overlayMode = '';
		$languageMode = '';
		if ($this->extConfig['sysLanguageMode'] == 'normal') {
			$languageMode = $GLOBALS['TSFE']->sys_language_content;
			$overlayMode = $GLOBALS['TSFE']->sys_language_contentOL;
		} else {
			$languageMode = $GLOBALS['TSFE']->sys_language_uid;
			$overlayMode = 'hideNonTranslated';
		}

		// record overlay (enables multilanguage support)
		$terms = array();
		while ($term = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			// get the translated record if the content language is not the default language
			if ($languageMode) {
				$term = $GLOBALS['TSFE']->sys_page->getRecordOverlay(
					'tx_content_replacer_term',
					$term,
					$languageMode,
					$overlayMode
				);
			}

			$terms[$term['term']] = $term;
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
	 * @param $termName original name of the term which is given to the stdWrap as an alternative for an empty replacement
	 * @return string prepared text
	 */
	protected function prepareTermReplacement($replacement, $stdWrap, $termName) {
		// rte transformation of the replacement string
		if ($replacement != '') {
			$replacement = $GLOBALS['TSFE']->cObj->parseFunc($replacement, $this->parseFunc);
			$replacement = preg_replace('/^<p>(.+)<\/p>$/s', '\1', $replacement);
		}

		// stdWrap execution if available
		if ($stdWrap != '') {
			$replacement = $GLOBALS['TSFE']->cObj->stdWrap(
				($replacement == '' ? $termName : $replacement),
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
