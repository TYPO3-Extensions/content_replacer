<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$TCA['tx_content_replacer_term'] = array (
	'ctrl' => $TCA['tx_content_replacer_term']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden,
			term, category_uid, stdWrap, replacement, description, starttime, endtime'
	),
	'types' => array (
		0 => array (
			'showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, term,
				category_uid, stdWrap,
				replacement;;;richtext[*]:rte_transform[mode=ts_css|imgpath=uploads/tx_content_parser/rte/],
				description, starttime, endtime, --div--'
		)
	),
	'palettes' => array (
		0 => array (
			'showitem' => ''
		),
	),
	'columns' => array (
		't3ver_label' => array (
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'sys_language_uid' => array (
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.php:LGL.language',
			'config' => array (
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array (
					array ('LLL:EXT:lang/locallang_general.php:LGL.allLanguages', -1),
					array ('LLL:EXT:lang/locallang_general.php:LGL.default_value', 0)
				),
			)
		),
		'l10n_parent' => array (
			'exclude' => true,
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config' => array (
				'type' => 'select',
				'items' => array (
					array ('', 0),
				),
				'foreign_table' => 'tx_content_replacer_term',
				'foreign_table_where' =>
					'AND tx_content_replacer_term.pid=###CURRENT_PID### ' .
					'AND tx_content_replacer_term.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => false
			)
		),
		'starttime' => array (
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0'
			)
		),
		'endtime' => array (
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config' => array (
				'type' => 'input',
				'size' => '8',
				'max' => '20',
				'eval' => 'date',
				'checkbox' => '0',
				'default' => '0',
				'range' => array (
					'upper' => mktime(0, 0, 0, 12, 31, 2020),
				)
			)
		),
		'term' => array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_term.term',
			'config' => array (
				'type' => 'input',
				'size' => 40,
				'max' => 256,
				'eval' => 'trim,required',
			)
		),
		'category_uid' => array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_term.category_uid',
			'config' => array (
				'type' => 'select',
				'internal_type' => 'db',
				'allowed' => 'tx_content_replacer_category',
				'foreign_table' => 'tx_content_replacer_category',
				'items' => array (
					array ('', 0),
				),
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
				'eval' => 'required',
				'wizards' => array (
					'add' => array (
						'type' => 'script',
						'title' => 'Creation of a new category!',
						'icon' => 'add.gif',
						'params' => array (
							'table' => 'tx_content_replacer_category',
							'pid' => '###CURRENT_PID###',
							'setValue' => 'prepend'
						),
						'script' => 'wizard_add.php',
					)
				)
			)
		),
		'stdWrap' => array (
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_term.stdWrap',
			'config' => array (
				'type' => 'input',
				'size' => 40,
				'max' => 256,
				'eval' => 'trim',
			)
		),
		'replacement' => array (
			'label' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_term.replacement',
			'config' => array (
				'type' => 'text',
				'cols' => 40,
				'rows' => 3
			)
		),
		'description' => array (
			'exclude' => true,
			'label' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_term.description',
			'config' => array (
				'type' => 'text',
				'cols' => 40,
				'rows' => 3
			)
		),
	)
);

$TCA['tx_content_replacer_category'] = array (
	'ctrl' => $TCA['tx_content_replacer_category']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'hidden, category, description'
	),
	'types' => array (
		0 => array (
			'showitem' => 'hidden;;1, category, description, --div--'
		)
	),
	'palettes' => array (
		0 => array (
			'showitem' => ''
		),
	),
	'columns' => array (
		't3ver_label' => array (
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.versionLabel',
			'config' => array (
				'type' => 'input',
				'size' => '30',
				'max'  => '30',
			)
		),
		'hidden' => array (
			'exclude' => true,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array (
				'type' => 'check',
				'default' => false
			)
		),
		'category' => array (
			'l10n_mode' => 'exclude',
			'label' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_category.category',
			'config' => array (
				'type' => 'input',
				'size' => 40,
				'max' => 256,
				'eval' => 'trim,required,unique',
			)
		),
		'description' => array (
			'exclude' => true,
			'label' => 'LLL:EXT:content_replacer/locallang_db.xml:tx_content_replacer_category.description',
			'config' => array (
				'type' => 'text',
				'cols' => 40,
				'rows' => 3
			)
		),
	)
);

?>
