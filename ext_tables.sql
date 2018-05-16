#
# Table structure for table 'tt_address'
#
CREATE TABLE tt_address (
    module_sys_dmail_html tinyint(3) unsigned NOT NULL DEFAULT '0',
    registeraddresshash varchar(40) DEFAULT '' NOT NULL,
    eigene_anrede varchar(255) DEFAULT '' NOT NULL,
    tx_directmailsubscription_localgender varchar(255) DEFAULT '' NOT NULL,
    consent_newsletter tinyint(3) DEFAULT '0',
    consent_time int(11) DEFAULT '0' NOT NULL
);
