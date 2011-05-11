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
 * Abstract parser that handles the parsing and(!) replacement of terms
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 * @package TYPO3
 * @subpackage content_replacer
 */
abstract class tx_contentreplacer_service_AbstractParser {
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
	 * @var tx_contentreplacer_repository_Term
	 */
	protected $termRepository = NULL;

	/**
	 * Constructor: Initializes the internal class properties.
	 *
	 * Note: The extension configuration array consists of the global and typoscript configuration.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->parseFunc = $GLOBALS['TSFE']->tmpl->setup['lib.']['parseFunc_RTE.'];
	}

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
	 * Injects the term repository
	 *
	 * @param tx_contentreplacer_repository_Term $repository
	 * @return void
	 */
	public function injectTermRepository(tx_contentreplacer_repository_Term $repository) {
		$this->termRepository = $repository;
		$this->termRepository->setExtensionConfiguration($this->extensionConfiguration);
	}

	/**
	 * Prepares a replacement value by applying the possible stdWrap and executing RTE
	 * transformations. The stdWrap typoscript object must be defined inside the extension
	 * namespace "plugin.tx_content_replacer".
	 *
	 * Note: If the replacement text is empty, we pass the term name as the initial content of the
	 * stdWrap object.
	 *
	 * @param string $replacement
	 * @param string $stdWrap
	 * @param string $termName
	 * @return string
	 */
	protected function prepareReplacementTerm($replacement, $stdWrap, $termName) {
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

	/**
	 * Enriches the given terms with information's from the database and returns the default
	 * term for any replacements.
	 *
	 * @param array $terms
	 * @return string
	 */
	protected function prepareFoundTerms(array &$terms) {
		$replacementTerms['*'] = array();
		$terms = array_keys($replacementTerms);
		$configuredTerms = $this->termRepository->fetchTerms($terms, $category);
		$terms = array_merge_recursive($replacementTerms, $configuredTerms);

			// define default replacement if no other term replacement is available
		$defaultReplacement = (is_array($terms['*']) ? $terms['*'] : '');
		unset($terms['*']);

		return $defaultReplacement;
	}

	/**
	 * This function parses the generated content from TYPO3 and returns an ordered list
	 * of terms with their related categories.
	 *
	 * @abstract
	 * @return array
	 */
	abstract public function parse();

	/**
	 * Replaces the given terms with their related replacement values.
	 *
	 * @abstract
	 * @param array $category
	 * @param array $terms
	 * @return void
	 */
	abstract public function replaceByCategory(array $category, array $terms);
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Service/class.tx_contentreplacer_service_AbstractParser.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Service/class.tx_contentreplacer_service_AbstractParser.php']);
}

?>