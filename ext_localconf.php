<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

if (TYPO3_MODE === 'FE') {
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-output'][] =
		'EXT:content_replacer/Classes/Controller/class.tx_contentreplacer_controller_main.php:tx_contentreplacer_controller_Main->contentPostProcOutput';

	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['contentPostProc-all'][] =
		'EXT:content_replacer/Classes/Controller/class.tx_contentreplacer_controller_main.php:tx_contentreplacer_controller_Main->contentPostProcAll';
}

?>