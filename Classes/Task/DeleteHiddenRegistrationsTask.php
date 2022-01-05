<?php
namespace AFM\Registeraddress\Task;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */


use AFM\Registeraddress\Service\DeleteHiddenRegistrationsService;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Scheduler\Task\AbstractTask;


class DeleteHiddenRegistrationsTask extends AbstractTask
{
    /**
     * Number of seconds which is the max age of hidden entries
     *
     * @var int
     */
    public $maxAge = 86400;

    /**
     * Table with address data
     *
     * @var string
     */
    public $table = 'tt_address';

    /**
     * Additionally log table and field
     *
     * @var string
     */
    public $logTableAndField = 'tx_registeraddresslogger_domain_model_logentry:address';

    /**
     * Remove entries from database instead of mark as deleted
     *
     * @var boolean
     */
    public $forceDelete = false;

    /**
     * Public method, called by scheduler.
     */
    public function execute() {
        $deleteHiddenRegistrations = GeneralUtility::makeInstance(DeleteHiddenRegistrationsService::class);

        /** @var FlashMessageService $flashMessageService */
        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $logTableAndFieldArray = explode(':', $this->logTableAndField, 2);

        if($this->logTableAndField && GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($logTableAndFieldArray[0])) {
            $countDeletedLogEntries = $deleteHiddenRegistrations->deleteLogEntries($this->forceDelete, $this->logTableAndField, $this->table, $this->maxAge);
            $flashMessageService->getMessageQueueByIdentifier()->addMessage(
                new FlashMessage(
                    sprintf(
                        $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.deleteLogSuccessMessage'),
                        $countDeletedLogEntries
                    ),
                    $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.deleteLogSuccessTitle'),
                    FlashMessage::OK
                )
            );
        }

        $countDeletedEntries = $deleteHiddenRegistrations->deleteEntries($this->forceDelete, 'tt_address', $this->maxAge);
        $flashMessageService->getMessageQueueByIdentifier()->addMessage(
            new FlashMessage(
                sprintf(
                    $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.deleteSuccessMessage'),
                    $countDeletedEntries
                ),
                $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.deleteSuccessTitle'),
                FlashMessage::OK
            )
        );
        return true;
    }
    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
        $message = '';
        if($this->table){
            $message .= $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.table') . ': ' . $this->table . '. ';
        }
        if($this->maxAge){
            $message .= $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.maxAge') . ': ' . $this->maxAge . '. ';
        }
        if($this->logTableAndField){
            $message .= $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.logTableAndField') . ': ' . $this->logTableAndField . '. ';
        }
        if($this->forceDelete){
            $message .= $this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.force-delete.active') . ' ';
        }
        return $message;
    }
}
