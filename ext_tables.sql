-- -----------------------------------------------------
-- Table tx_content_replacer_term
-- -----------------------------------------------------
CREATE TABLE tx_content_replacer_term (
	uid int(10) unsigned NOT NULL auto_increment,
	pid int(10) unsigned NOT NULL DEFAULT '0',

	tstamp int(10) unsigned NOT NULL DEFAULT '0',
	crdate int(10) unsigned NOT NULL DEFAULT '0',
	cruser_id int(11) NOT NULL DEFAULT '0',

	t3ver_oid int(11) NOT NULL DEFAULT '0',
	t3ver_id int(11) NOT NULL DEFAULT '0',
	t3ver_wsid int(11) NOT NULL DEFAULT '0',
	t3ver_label varchar(30) NOT NULL DEFAULT '',
	t3ver_state tinyint(4) NOT NULL DEFAULT '0',
	t3ver_stage tinyint(4) NOT NULL DEFAULT '0',
	t3ver_count int(11) NOT NULL DEFAULT '0',
	t3ver_tstamp int(11) NOT NULL DEFAULT '0',
	t3_origuid int(11) NOT NULL DEFAULT '0',

    sys_language_uid int(11) NOT NULL DEFAULT '0',
	l10n_parent int(11) NOT NULL DEFAULT '0',
	l10n_diffsource mediumblob NOT NULL,

	deleted tinyint(1) NOT NULL DEFAULT '0',
    hidden tinyint(1) NOT NULL DEFAULT '0',

	starttime int(10) unsigned DEFAULT '0' NOT NULL,
	endtime int(10) unsigned DEFAULT '0' NOT NULL,

	term varchar(255) NOT NULL DEFAULT '',
	category_uid int(10) unsigned NOT NULL DEFAULT '0',
	stdWrap varchar(255) NOT NULL DEFAULT '',
	replacement text,
	description text,

	PRIMARY KEY (uid),
	INDEX parent (pid,sys_language_uid),
	INDEX category (category_uid),
	INDEX t3ver_oid (t3ver_oid,t3ver_wsid),
	INDEX terms (term)
);

-- -----------------------------------------------------
-- Table tx_content_replacer_category
-- -----------------------------------------------------
CREATE TABLE tx_content_replacer_category (
	uid int(10) unsigned NOT NULL auto_increment,
	pid int(10) unsigned NOT NULL DEFAULT '0',

	tstamp int(10) unsigned NOT NULL DEFAULT '0',
	crdate int(10) unsigned NOT NULL DEFAULT '0',
	cruser_id int(11) NOT NULL DEFAULT '0',

	t3ver_oid int(11) NOT NULL DEFAULT '0',
	t3ver_id int(11) NOT NULL DEFAULT '0',
	t3ver_wsid int(11) NOT NULL DEFAULT '0',
	t3ver_label varchar(30) NOT NULL DEFAULT '',
	t3ver_state tinyint(4) NOT NULL DEFAULT '0',
	t3ver_stage tinyint(4) NOT NULL DEFAULT '0',
	t3ver_count int(11) NOT NULL DEFAULT '0',
	t3ver_tstamp int(11) NOT NULL DEFAULT '0',
	t3_origuid int(11) NOT NULL DEFAULT '0',

	deleted tinyint(1) NOT NULL DEFAULT '0',
    hidden tinyint(1) NOT NULL DEFAULT '0',

	category varchar(255) NOT NULL DEFAULT '',
	description text,

	PRIMARY KEY (uid),
	INDEX parent (pid),
	INDEX t3ver_oid (t3ver_oid,t3ver_wsid)
);