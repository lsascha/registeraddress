<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$_EXTKEY = 'registeraddress';

// Register Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \AFM\Registeraddress\Hook\DataHandlerHook::class;

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'AFM.' . $_EXTKEY,
	'Registerform',
	array(
		'Address' => 'new, create, approve, edit, update, delete, information',

	),
	// non-cacheable actions
	array(
		'Address' => 'new, create, approve, edit, update, delete, information',

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


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'AFM.' . $_EXTKEY,
	'RegisterformUnsubscribe',
	array(
		'Address' => 'unsubscribeForm',

	),
	// non-cacheable actions
	array(
		'Address' => 'unsubscribeForm',

	)
);
