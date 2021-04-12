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
            ->setMaxResults(10)
            ->execute();
        return $query;
    }
}
