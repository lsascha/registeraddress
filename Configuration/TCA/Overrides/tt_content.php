<?php
defined('TYPO3') or die();

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['registeraddress_registerform'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('registeraddress_registerform',
    'FILE:EXT:registeraddress/Configuration/FlexForms/flexform_registration.xml');
