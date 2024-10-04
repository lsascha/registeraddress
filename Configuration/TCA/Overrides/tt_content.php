<?php
defined('TYPO3') or die();

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['registeraddress_registerform'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('registeraddress_registerform',
    'FILE:EXT:registeraddress/Configuration/FlexForms/flexform_registration.xml');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('Registeraddress', 'Registerform', 'Registration Form');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('Registeraddress', 'RegisterformRedirect', 'Registration Form (only redirects)');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('Registeraddress', 'RegisterformUnsubscribe', 'Registration Form to unsubscribe');
