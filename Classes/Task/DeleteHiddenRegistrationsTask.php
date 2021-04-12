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


use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extensionmanager\Utility\Repository\Helper;
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
     * Number of seconds which is the max age of hidden entries
     *
     * @var string
     */
    public $table = 'tt_address';

    /**
     * Public method, called by scheduler.
     */
    public function execute() {
        $deleteHiddenRegistrations = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\AFM\Registeraddress\Service\DeleteHiddenRegistrationsService::class);
        $deleteHiddenRegistrations->selectEntries('tt_address', $this->maxAge);
        return true;
    }
    /**
     * This method returns the sleep duration as additional information
     *
     * @return string Information to display
     */
    public function getAdditionalInformation()
    {
         $infoMaxAge = [$this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.maxAge'), $this->maxAge];
         $infoTable = [$this->getLanguageService()->sL('LLL:EXT:registeraddress/Resources/Private/Language/locallang_db.xlf:scheduler.table'), $this->table];

        return  $infoTable[0] . ': '. $infoTable[1] . ', ' . $infoMaxAge[0] . ': ' . $infoMaxAge[1] . '.';
    }
}
