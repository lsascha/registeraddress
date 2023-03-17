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


