<?php

if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

/** @var $_EXTKEY string */
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Content Replacer');

t3lib_extMgm::allowTableOnStandardPages('tx_content_replacer_term');
t3lib_extMgm::addToInsertRecords('tx_content_replacer_term');
$TCA['tx_content_replacer_term'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_term',
		'label' => 'term',
		'label_alt' => 'category_uid',
		'label_alt_force' => TRUE,
		'dividers2tabs' => TRUE,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'default_sortby' => 'ORDER BY category_uid, term',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'resources/icons/icon_tx_content_replacer_term.png',
	),
);

t3lib_extMgm::allowTableOnStandardPages('tx_content_replacer_category');
t3lib_extMgm::addToInsertRecords('tx_content_replacer_category');
$TCA['tx_content_replacer_category'] = array(
	'ctrl' => array(
		'title' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_category',
		'label' => 'category',
		'dividers2tabs' => TRUE,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => TRUE,
		'origUid' => 't3_origuid',
		'default_sortby' => 'ORDER BY category',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden'
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) . 'resources/icons/icon_tx_content_replacer_category.png',
	),
);

?>