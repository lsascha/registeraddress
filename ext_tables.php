<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Registeraddress',
    'Registerform',
    'Registration Form'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Registeraddress',
	'RegisterformRedirect',
	'Registration Form (only redirects)'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	'Registeraddress',
	'RegisterformUnsubscribe',
	'Registration Form to unsubscribe'
);


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('registeraddress', 'Configuration/TypoScript', 'registerttaddress');

//\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tt_address', 'EXT:registeraddress/Resources/Private/Language/locallang_csh_tt_address.xlf');


