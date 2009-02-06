<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE == 'FE') {
	$extConfig_tx_content_replacer = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['content_replacer']);

	// the hook depends on the no_cache parameter
	if (t3lib_div::_GP('no_cache') || $extConfig_tx_content_replacer['useAllHook']) {
		$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] =
			'EXT:content_replacer/class.tx_content_replacer.php:tx_content_replacer->main';
	} else {
		$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-cached'][] =
			'EXT:content_replacer/class.tx_content_replacer.php:tx_content_replacer->main';
	}
}

?>
