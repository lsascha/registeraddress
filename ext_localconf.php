<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'AFM.' . $_EXTKEY,
	'Registerform',
	array(
		'Address' => 'new, create, approve, edit, update, delete, information',
		
	),
	// non-cacheable actions
	array(
		'Address' => 'new, create, approve, edit, update, delete',
		
	)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'AFM.' . $_EXTKEY,
	'RegisterformRedirect',
	array(
		'Address' => 'new',
		
	),
	// non-cacheable actions
	array(
		'Address' => 'new',
		
	)
);

?>