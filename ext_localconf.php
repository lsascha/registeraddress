<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Register Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \AFM\Registeraddress\Hook\DataHandlerHook::class;

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'AFM.Registeraddress',
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
	'AFM.Registeraddress',
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
	'AFM.Registeraddress',
	'RegisterformUnsubscribe',
	array(
		'Address' => 'unsubscribeForm',

	),
	// non-cacheable actions
	array(
		'Address' => 'unsubscribeForm',

	)
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\AFM\Registeraddress\Task\DeleteHiddenRegistrationsTask::class] = [
    'extension' => 'registeraddress',
    'title' => 'DeleteHiddenRegistrations',
    'description' => 'Delete old, hidden registrations',
    'additionalFields' => \AFM\Registeraddress\Task\DeleteHiddenRegistrationsTaskAdditionalFieldProvider::class,
];
