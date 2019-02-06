<?php
defined('TYPO3_MODE') or die();

$fields = array(
    'eigene_anrede' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xml:tx_registeraddress_domain_model_address.eigene_anrede',
        'config' => [
            'type'     => 'input',
            'size'     => 30,
            'eval' => 'trim'
        ],
    ],
    'consent' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xml:tx_registeraddress_domain_model_address.consent',
        'config' => [
            'type'     => 'text',
            'readOnly' => 1,
            'size'     => 30,
            'eval' => 'trim'
        ],
    ],
    'registeraddress_language' => [
        'exclude' => true,
        'label' => 'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.language',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'special' => 'languages',
            'items' => [
                [
                    'LLL:EXT:lang/Resources/Private/Language/locallang_general.xlf:LGL.allLanguages',
                    -1,
                    'flags-multiple'
                ],
            ],
            'default' => 0,
        ]
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $fields, TRUE);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'eigene_anrede', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_address', 'eigene_anrede', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'consent,registeraddress_language','','after:description');
