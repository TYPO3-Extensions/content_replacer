<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2009-2011 Stefan Galinski <stefan.galinski@gmail.com>
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
 * Controlling code of the extension "content_replacer"
 *
 * @author Stefan Galinski <stefan.galinski@gmail.com>
 * @package TYPO3
 * @subpackage content_replacer
 */
class tx_contentreplacer_controller_Main {
	/**
	 * Extension Configuration
	 *
	 * @var array
	 */
	protected $extensionConfiguration = array();

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
		$this->extensionConfiguration = $this->prepareConfiguration();
		$this->initTermRepository();
	}

	/**
	 * Initializes the term repository
	 *
	 * @return void
	 */
	public function initTermRepository() {
		/** @var $termRepository tx_contentreplacer_repository_Term */
		$this->termRepository = t3lib_div::makeInstance('tx_contentreplacer_repository_Term');
		$this->termRepository->setExtensionConfiguration($this->extensionConfiguration);
	}

	/**
	 * Returns the merged extension configuration of the global configuration and the typoscript
	 * settings.
	 *
	 * @return array
	 */
	public function prepareConfiguration() {
		$extensionConfiguration = array();
		if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer'])) {
			$extensionConfiguration = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer']);
		}

		$typoscriptConfiguration = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_content_replacer.'];
		if (is_array($typoscriptConfiguration)) {
			foreach ($typoscriptConfiguration as $key => $value) {
				$extensionConfiguration[$key] = $value;
			}
		}

		return $extensionConfiguration;
	}

	/**
	 * Just a wrapper for the main function! It's used for the contentPostProc-output hook.
	 *
	 * This hook is executed if the page contains *_INT objects! It's called always at the
	 * last hook before the final output. This isn't the case if you are using a
	 * static file cache like "nc_staticfilecache".
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
	 * Returns the span tag parser
	 *
	 * @return tx_contentreplacer_service_SpanParser
	 */
	protected function getSpanParser() {
		/** @var $spanParser tx_contentreplacer_service_SpanParser */
		$spanParser = t3lib_div::makeInstance('tx_contentreplacer_service_SpanParser');
		$spanParser->setExtensionConfiguration($this->extensionConfiguration);
		$spanParser->injectTermRepository($this->termRepository);

		return $spanParser;
	}

	/**
	 * Parses and replaces the content several times until the given parser cannot find
	 * any more occurences or the maximum amount of possible passes is reached.
	 *
	 * @param tx_contentreplacer_service_AbstractParser $parser
	 * @return void
	 */
	protected function parseAndReplace(tx_contentreplacer_service_AbstractParser $parser) {
		$loopCounter = 0;
		while (TRUE) {
				// recursion check to prevent endless loops
			++$loopCounter;
			if ($loopCounter > $this->extensionConfiguration['amountOfPasses']) {
				break;
			}

				// no further occurrences  => break the loop to save performance
			$occurences = $parser->parse();
			if (!count($occurences)) {
				break;
			}

				// replace the terms category by category
			foreach ($occurences as $category => $terms) {
				$parser->replaceByCategory($category, $terms);
			}
		}
	}

	/**
	 * Controlling code
	 *
	 * @return void
	 */
	protected function main() {
		$spanParser = $this->getSpanParser();
		$this->parseAndReplace($spanParser);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Controller/class.tx_contentreplacer_controller_main.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/content_replacer/Classes/Controller/class.tx_contentreplacer_controller_main.php']);
}

?>