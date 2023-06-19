<?php
if (!defined('TYPO3')) {
    die ('Access denied.');
}

// Register Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \AFM\Registeraddress\Hook\DataHandlerHook::class;

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Registeraddress',
    'Registerform',
    array(
        \AFM\Registeraddress\Controller\AddressController::class => 'new, create, approve, edit, update, delete, information',

    ),
    // non-cacheable actions
    array(
        \AFM\Registeraddress\Controller\AddressController::class => 'new, create, approve, edit, update, delete, information',

    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Registeraddress',
    'RegisterformRedirect',
    array(
        \AFM\Registeraddress\Controller\AddressController::class => 'new',

    ),
    // non-cacheable actions
    array(
        \AFM\Registeraddress\Controller\AddressController::class => 'new',

    )
);


\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Registeraddress',
    'RegisterformUnsubscribe',
    array(
        \AFM\Registeraddress\Controller\AddressController::class => 'unsubscribeForm',

    ),
    // non-cacheable actions
    array(
        \AFM\Registeraddress\Controller\AddressController::class => 'unsubscribeForm',

    )
);

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\AFM\Registeraddress\Task\DeleteHiddenRegistrationsTask::class] = [
    'extension' => 'registeraddress',
    'title' => 'DeleteHiddenRegistrations',
    'description' => 'Delete old, hidden registrations',
    'additionalFields' => \AFM\Registeraddress\Task\DeleteHiddenRegistrationsTaskAdditionalFieldProvider::class,
];

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['registeraddress_createAddressHash']
    = \AFM\Registeraddress\Update\CreateAddressHashUpdate::class;
