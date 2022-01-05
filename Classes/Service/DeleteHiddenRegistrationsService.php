<?php
declare(strict_types=1);

namespace AFM\Registeraddress\Service;


use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DeleteHiddenRegistrationsService
{
    /*
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function selectEntries(string $table = 'tt_address', int $maxAge = 86400) {
        $limit = time() - $maxAge;
        $hiddenField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];

        /**@var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder**/
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $query = $queryBuilder
            ->select('uid','pid','email')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq($hiddenField, 1),
                $queryBuilder->expr()->lt('crdate', $limit)
            )
            ->execute();
        return $query;
    }
    /*
     * @return \Doctrine\DBAL\Driver\Statement|int
     */
    public function deleteEntries($forceDelete, string $table = 'tt_address', int $maxAge = 86400) {
        $limit = time() - $maxAge;
        $hiddenField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['disabled'];
        $deleteField = $GLOBALS['TCA'][$table]['ctrl']['delete'];

        /**@var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder**/
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($table);
        if($forceDelete) {
            $query = $queryBuilder
                ->delete($table)
                ->where(
                    $queryBuilder->expr()->eq($hiddenField, 1),
                    $queryBuilder->expr()->eq($deleteField, 0),
                    $queryBuilder->expr()->lt('crdate', $limit)
                )
                ->execute();
        } else {
            $query = $queryBuilder
                ->update($table)
                ->where(
                    $queryBuilder->expr()->eq($hiddenField, 1),
                    $queryBuilder->expr()->eq($deleteField, 0),
                    $queryBuilder->expr()->lt('crdate', $limit)
                )
                ->set($deleteField, 1)
                ->execute();
        }
        return $query;
    }

    /*
     * @return int
     */
    public function deleteLogEntries(
        $forceDelete,
        string $logTableAndField,
        string $table,
        int $maxAge
    )
    {
        $logTableAndFieldArray = explode(':', $logTableAndField, 2);
        $logTable = $logTableAndFieldArray[0];
        $logRelationField = $logTableAndFieldArray[1];
        /**@var $queryBuilder \TYPO3\CMS\Core\Database\Query\QueryBuilder**/
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($logTable);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $deleteField = $GLOBALS['TCA'][$logTable]['ctrl']['delete'];

        $selectedData = $this->selectEntries($table,$maxAge);
        $result = $selectedData->fetchAllAssociative();
        $logCounter = 0;
        foreach ($result as $address) {
            if($forceDelete) {
                $numberOfDeletedRows = $queryBuilder
                    ->delete($logTable)
                    ->where(
                        $queryBuilder->expr()->eq($logRelationField, $queryBuilder->createNamedParameter($address['uid'], \PDO::PARAM_INT))
                    )
                    ->execute();
            } else {
                $numberOfDeletedRows = $queryBuilder
                    ->update($logTable)
                    ->where(
                        $queryBuilder->expr()->eq($logRelationField, $queryBuilder->createNamedParameter($address['uid'], \PDO::PARAM_INT))
                    )
                    ->set($deleteField, 1)
                    ->execute();
            }
            $logCounter = $logCounter + $numberOfDeletedRows;
        }
        return $logCounter;
    }
}
