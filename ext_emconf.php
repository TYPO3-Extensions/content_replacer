<?php

########################################################################
# Extension Manager/Repository config file for ext "content_replacer".
#
# Auto generated 19-08-2010 21:55
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Content Replacer',
	'description' => 'You need a fast substitution of terms with full support of typoscript, categories and RTE integration? If yes, the extension could be perfectly fit into your project. The performance is gained by some manual work in the content by wrapping the replacement terms.',
	'category' => 'fe',
	'shy' => 0,
	'version' => '1.4.0',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
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
			'typo3' => '4.4.0-4.5.99',
			'php' => '5.2.0-5.3.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:18:{s:19:"de.locallang_db.xml";s:4:"1c99";s:21:"ext_conf_template.txt";s:4:"6320";s:12:"ext_icon.gif";s:4:"8fdb";s:17:"ext_localconf.php";s:4:"4086";s:14:"ext_tables.php";s:4:"c5eb";s:14:"ext_tables.sql";s:4:"07c8";s:16:"locallang_db.xml";s:4:"cb24";s:7:"tca.php";s:4:"1b6d";s:37:"classes/class.tx_content_replacer.php";s:4:"b371";s:14:"doc/manual.sxw";s:4:"2bd8";s:53:"resources/icons/icon_tx_content_replacer_category.png";s:4:"dd7a";s:56:"resources/icons/icon_tx_content_replacer_category__h.png";s:4:"ba3c";s:56:"resources/icons/icon_tx_content_replacer_category__t.png";s:4:"dbe8";s:49:"resources/icons/icon_tx_content_replacer_term.png";s:4:"448e";s:52:"resources/icons/icon_tx_content_replacer_term__h.png";s:4:"20f0";s:52:"resources/icons/icon_tx_content_replacer_term__t.png";s:4:"0f21";s:20:"static/constants.txt";s:4:"5173";s:16:"static/setup.txt";s:4:"f383";}',
	'suggests' => array(
	),
);

?>