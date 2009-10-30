<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE == 'FE') {
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] =
		'EXT:content_replacer/class.tx_content_replacer.php:tx_content_replacer->contentPostProcOutput';
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] =
		'EXT:content_replacer/class.tx_content_replacer.php:tx_content_replacer->contentPostProcAll';
}

?>
