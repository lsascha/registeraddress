<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3_MODE') or die();

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['registeraddress_registerform'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue('registeraddress_registerform',
    'FILE:EXT:registeraddress/Configuration/FlexForms/flexform_registration.xml');

ExtensionUtility::registerPlugin(
    'registeraddress',
    'Registerform',
    'Registration Form'
);

ExtensionUtility::registerPlugin(
    'registeraddress',
    'RegisterformRedirect',
    'Registration Form (only redirects)'
);

ExtensionUtility::registerPlugin(
    'registeraddress',
    'RegisterformUnsubscribe',
    'Registration Form to unsubscribe'
);