<?php
defined('TYPO3_MODE') or die();

$fields = array(
    'eigene_anrede' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xml:tx_registeraddress_domain_model_address.eigene_anrede',
        'config' => array(
            'type'     => 'input',
            'size'     => 30,
            'eval' => 'trim'
        ),
    ),
    'consent' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xml:tx_registeraddress_domain_model_address.consent',
        'config' => array(
            'type'     => 'text',
            'readOnly' => 1,
            'size'     => 30,
            'eval' => 'trim'
        ),
    ),
    'registeraddresshash' => array(
        'exclude' => 0,
        'label' => 'registeraddresshash',
        'config' => array(
            'type'     => 'text',
            'size'     => 30,
            'eval' => 'trim'
        ),
    ),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $fields, TRUE);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'eigene_anrede', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_address', 'eigene_anrede', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'consent','','after:description');
