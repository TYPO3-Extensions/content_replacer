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
 * Repository for fetching terms
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 * @package TYPO3
 * @subpackage content_replacer
 */
class tx_contentreplacer_repository_Term {
	/**
	 * @var array
	 */
	protected $extensionConfiguration = array();

	/**
	 * Sets the extension configuration
	 *
	 * @param array $extensionConfiguration
	 * @return void
	 */
	public function setExtensionConfiguration(array $extensionConfiguration) {
		$this->extensionConfiguration = $extensionConfiguration;
	}

	/**
	 * Returns the given terms with their related information's.
	 *
	 * @param array $filterTerms
	 * @param string $category
	 * @return array
	 */
	public function fetchTerms(array $filterTerms, $category) {
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
		$queryResource = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
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
					'tx_content_replacer_term', $term, $languageMode, $overlayMode
				);
			}

			$terms[$term['term']] = $term;
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($queryResource);

		return $terms;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Repository/class.tx_contentreplacer_repository_term.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Repository/class.tx_contentreplacer_repository_term.php']);
}

?>