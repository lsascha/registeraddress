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
    )
);

$dsgvoFields = array(
    'consent_newsletter' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xml:tx_registeraddress_domain_model_address.consent_newsletter',
        'config' => array(
            'type'     => 'none',
        )
    ),
    'consent_time' => array(
        'exclude' => 0,
        'label' => 'LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xml:tx_registeraddress_domain_model_address.consent_time',
        'config' => array(
            'type'     => 'none',
            'format' => 'datetime'
        )
    )
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $fields, TRUE);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'eigene_anrede', '', 'after:name');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_address', 'eigene_anrede', '', 'after:name');


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $dsgvoFields, TRUE);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'consent_newsletter', '', 'after:birthday');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'consent_time', '', 'after:consent_newsletter');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('tt_address', 'consent_newsletter', 'consent_newsletter,consent_time', 'after:birthday');
