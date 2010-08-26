<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2009-2010 Stefan Galinski <stefan.galinski@gmail.com>
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
 * Methods for the substition of specified terms
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 */
class tx_content_replacer {
	/**
	 * Extension Configuration
	 *
	 * @var array
	 */
	protected $extensionConfiguration = array();

	/**
	 * lib.parseFunc_RTE configuration
	 *
	 * @var array
	 */
	protected $parseFunc = array();

	/**
	 * Constructor: Initializes the internal class properties.
	 *
	 * Note: The extension configuration array consists of the global and typoscript configuration.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];

		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer'])) {
			$this->extensionConfiguration = unserialize(
				$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer']
			);
		}

		$typoscriptConfiguration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_content_replacer.'];
		if (is_array($typoscriptConfiguration)) {
			foreach ($typoscriptConfiguration as $key => $value) {
				$this->extensionConfiguration[$key] = $value;
			}
		}
	}

	/**
	 * Just a wrapper for the main function! It's used for the contentPostProc-output hook.
	 *
	 * This hook is executed if the page contains *_INT objects! It's called always at the
	 * last hook before the final output. This isn't the case if you are using a
	 * static file cache like nc_staticfilecache.
	 * 
	 * @return void
	 */
	public function contentPostProcOutput() {
		if (!$GLOBALS['TSFE']->isINTincScript() || $this->extensionConfiguration['disable']) {
			return;
		}

		$this->main();
	}

	/**
	 * Just a wrapper for the main function!  It's used for the contentPostProc-all hook.
	 *
	 * The hook is only executed if the page doesn't contains any *_INT objects. It's called
	 * always if the page wasn't cached or for the first hit!
	 *
	 * @return void
	 */
	public function contentPostProcAll() {
		if ($GLOBALS['TSFE']->isINTincScript() || $this->extensionConfiguration['disable']) {
			return;
		}

		$this->main();
	}

	/**
	 * Controlling code
	 *
	 * @return void
	 */
	protected function main() {
		$loopCounter = 0;
		while (TRUE) {
				// recursion check to prevent endless loops
			++$loopCounter;
			if ($loopCounter > $this->extensionConfiguration['amountOfPasses']) {
				break;
			}

				// no further occurences => break the loop to save performance
			$occurences = $this->parseContent();
			if (!count($occurences)) {
				break;
			}

				// replace the terms category by category
			foreach ($occurences as $category => $terms) {
				$this->replaceTermsByCategory($category, $terms);
			}
		}
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
	 * Each term has some additional properties:
	 * - pre: attributes before the class attribute
	 * - post: attributes after the class attribute
	 * - classAttribute: the class attribute without the replacement class
	 *
	 * @return array 
	 */
	protected function parseContent() {
			// fetch terms
		$matches = array();
		$prefix = preg_quote($this->extensionConfiguration['prefix'], '/');
		$pattern = '/' .
			'<span' .			// This expression includes any span nodes and parses
				'(?=[^>]+' .	// any attributes of the beginning start tag.
					'(?=(class="([^"]*?' . $prefix . '[^"]+?)"))' .
				')' .			// Use only spans which start with the defined class prefix
			' (.*?)\1(.*?)>' .	// and stop if the closing character is reached.
			'(.*?)<\/span>' .	// Finally we fetch the span content!
			'/is';
		preg_match_all($pattern, $GLOBALS['TSFE']->content, $matches);

			// order terms by category
		$categories = array();
		foreach ($matches[5] as $index => $term) {
			$term = trim($term);

				// select the css class with the category (defined by the prefix)
			$category = '';
			$classes = explode(' ', $matches[2][$index]);
			foreach ($classes as $classIndex => $class) {
				$class = trim($class);

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

				// add the category/term with some additional informations
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
	 * @see parseContent() for the array structure
	 * @param $category array
	 * @param $terms array
	 * @return void
	 */
	protected function replaceTermsByCategory($category, $replacementTerms) {
			// fetch term informations
		$replacementTerms['*'] = array();
		$terms = array_keys($replacementTerms);
		$terms = array_merge_recursive(
			$replacementTerms,
			$this->fetchTerms($terms, $category)
		);

			// if the wildcard term was defined for the category, then we use it
			// as the default replacement object
		$defaultReplacement = '';
		if (is_array($terms['*'])) {
			$defaultReplacement = $terms['*'];
		}
		unset($terms['*']);

			// loop terms
		$search = $replace = array();
		foreach($terms as $termName => $term) {
				// if the term wasn't defined in the database, we are using the default
				// replacement object (wildcard term or an empty string)
			if (!isset($term['uid'])) {
				$term = array_merge((array)$term, $defaultReplacement);
				$term['term'] = $termName;
			}

				// built regular expression for this term
			$searchClass = preg_quote($this->extensionConfiguration['prefix'] . $category, '/');
			$search[$termName] = '/' .
				'<span ' . preg_quote($term['pre'], '/') .
				'class="([^"]*?)' . $searchClass . '([^"]*?)"' .
				preg_quote($term['post'], '/') . '>' .
				'\s*?' . preg_quote($term['term'], '/') . '\s*?' .
				'<\/span>'.
			'/i';

				// built replacement text for this term
			$replace[$termName] = $this->prepareTermReplacement(
				$term['replacement'],
				trim($term['stdWrap']),
				$termName
			);

			if (trim($term['pre']) !== ''
				|| trim($term['post']) !== ''
				|| trim($term['classAttribute']) !== ''
			) {
				
				$attributes = trim(
					$term['pre'] . ' ' .
					$term['post'] . ' ' .
					$term['classAttribute']
				);

				$replace[$termName] = '<span ' . $attributes . '>' . $replace[$termName] . '</span>';
			}
		}

			// replace all terms by multiple regular expressions
		$GLOBALS['TSFE']->content = preg_replace(
			$search,
			$replace,
			$GLOBALS['TSFE']->content
		);
	}

	/**
	 * Returns the given terms with their related informations.
	 *
	 * @param $filterTerms array
	 * @param $category string
	 * @return array
	 */
	protected function fetchTerms($filterTerms, $category) {
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

		$GLOBALS['TYPO3_DB']->debugOutput = FALSE;
		$queryResource = $GLOBALS['TYPO3_DB']->exec_SELECTquery (
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
		if ($this->extensionConfiguration['sysLanguageMode'] === 'normal') {
			$languageMode = $GLOBALS['TSFE']->sys_language_content;
			$overlayMode = $GLOBALS['TSFE']->sys_language_contentOL;
		} else {
			$languageMode = $GLOBALS['TSFE']->sys_language_uid;
			$overlayMode = 'hideNonTranslated';
		}

			// overlay record with an other language if required
		$terms = array();
		while ($term = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($queryResource)) {
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
		$GLOBALS['TYPO3_DB']->sql_free_result($queryResource);
		
		return $terms;
	}

	/**
	 * Prepares a replacement value by applying the possible stdWrap and executing RTE
	 * transformations. The stdWrap typoscript object must be defined inside the extension
	 * namespace "plugin.tx_content_replacer".
	 *
	 * Note: If the replacement text is empty, we pass the term name as the initial content of the
	 * stdWrap object.
	 *
	 * @param $replacement string
	 * @param $stdWrap string
	 * @param $termName string
	 * @return string
	 */
	protected function prepareTermReplacement($replacement, $stdWrap, $termName) {
			// rte transformation (the surrounding p tags are removed afterwards)
		if ($replacement !== '') {
			$replacement = $GLOBALS['TSFE']->cObj->parseFunc($replacement, $this->parseFunc);
			$replacement = preg_replace('/^<p>(.+)<\/p>$/s', '\1', $replacement);
		}

			// stdWrap transformation
		if ($stdWrap !== '') {
			$replacement = $GLOBALS['TSFE']->cObj->stdWrap(
				($replacement === '' ? $termName : $replacement),
				$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_content_replacer.'][$stdWrap . '.']
			);
		}

		return $replacement;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/content_replacer/classes/class.tx_content_replacer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/content_replacer/classes/class.tx_content_replacer.php']);
}

?>
