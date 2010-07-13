<?php

########################################################################
# Extension Manager/Repository config file for ext "content_replacer".
#
# Auto generated 13-07-2010 12:20
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Content Replacer',
	'description' => 'This extension parses your content and replaces keywords in special span tags with a defined replacement. It supports multilanguage, categories, wildcard terms, RTE insertion and stdWrap properties on the replacement text.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.3.2',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => 'uploads/tx_content_parser/rte',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Stefan Galinski',
	'author_email' => 'stefan.galinski@gmail.com',
	'author_company' => 'domainFACTORY',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'typo3' => '4.0.0-4.2.99',
			'php' => '5.0.0-5.2.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'tinymce_rte' => '-',
		),
	),
	'_md5_values_when_last_written' => 'a:18:{s:29:"class.tx_content_replacer.php";s:4:"db73";s:19:"de.locallang_db.xml";s:4:"5314";s:21:"ext_conf_template.txt";s:4:"6320";s:12:"ext_icon.gif";s:4:"8fdb";s:17:"ext_localconf.php";s:4:"c204";s:14:"ext_tables.php";s:4:"c5eb";s:14:"ext_tables.sql";s:4:"07c8";s:16:"locallang_db.xml";s:4:"7543";s:7:"tca.php";s:4:"d19b";s:14:"doc/manual.sxw";s:4:"f3eb";s:53:"resources/icons/icon_tx_content_replacer_category.png";s:4:"dd7a";s:56:"resources/icons/icon_tx_content_replacer_category__h.png";s:4:"ba3c";s:56:"resources/icons/icon_tx_content_replacer_category__t.png";s:4:"dbe8";s:49:"resources/icons/icon_tx_content_replacer_term.png";s:4:"448e";s:52:"resources/icons/icon_tx_content_replacer_term__h.png";s:4:"20f0";s:52:"resources/icons/icon_tx_content_replacer_term__t.png";s:4:"0f21";s:20:"static/constants.txt";s:4:"dd40";s:16:"static/setup.txt";s:4:"ef8f";}',
	'suggests' => array(
	),
);

?>