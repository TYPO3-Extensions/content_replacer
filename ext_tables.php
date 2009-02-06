<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// allow items on standard pages
t3lib_extMgm::allowTableOnStandardPages('tx_content_replacer_term');
t3lib_extMgm::addToInsertRecords('tx_content_replacer_term');
t3lib_extMgm::allowTableOnStandardPages('tx_content_replacer_category');
t3lib_extMgm::addToInsertRecords('tx_content_replacer_category');

// initialize static extension templates
t3lib_extMgm::addStaticFile($_EXTKEY, 'static/', 'Content Replacer');

// TCA: table definitions
$TCA['tx_content_replacer_term'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_term',
		'label' => 'term',
		'label_alt' => 'category_uid',
		'label_alt_force' => true,
		'dividers2tabs' => true,
		'languageField' => 'sys_language_uid',
		'transOrigPointerField'  => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => true,
		'origUid' => 't3_origuid',
		'default_sortby' => 'ORDER BY category_uid, term',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) .
			'resources/icons/icon_tx_content_replacer_term.png',
	),
);

$TCA['tx_content_replacer_category'] = array (
	'ctrl' => array (
		'title' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_category',
		'label' => 'category',
		'dividers2tabs' => true,
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'versioningWS' => true,
		'origUid' => 't3_origuid',
		'default_sortby' => 'ORDER BY category',
		'delete' => 'deleted',
		'enablecolumns' => array (
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY) . 'tca.php',
		'iconfile' => t3lib_extMgm::extRelPath($_EXTKEY) .
			'resources/icons/icon_tx_content_replacer_category.png',
	),
);
?>
